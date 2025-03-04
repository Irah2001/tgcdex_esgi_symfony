<?php

namespace App\Controller;

use App\Entity\Exchange;
use App\Entity\User;
use App\Repository\ExchangeRepository;
use App\Repository\PokemonCardRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExchangeController extends AbstractController
{
    private function array_containsall($array, $all)
    {
        foreach ($array as $v) {
            if (!in_array($v, $all)) return false;
        }
        return true;
    }

    #[Route('/exchange', name: 'exchange')]
    public function index(ExchangeRepository $exchangeRepository): Response
    {
        $exchanges = $exchangeRepository->findAll();
        $exchanges = array_filter($exchanges, function ($ex) {
            return $ex->getExecutedAt() == null;
        });

        return $this->render('exchange/index.html.twig', [
            'controller_name' => 'ExchangeController',
            'exchanges' => $exchanges,
        ]);
    }

    #[Route('/exchange/{id}', name: 'app_exchange_details')]
    public function details(?Exchange $exchange): Response
    {
        if (!$exchange) return $this->redirectToRoute("app_exchange");

        return $this->render('exchange/details/index.html.twig', [
            'controller_name' => 'ExchangeController',
            'exchange' => $exchange,
        ]);
    }

    #[Route('/exchange/{id}/accept', name: 'app_exchange_accept')]
    public function accept(?Exchange $exchange, ExchangeRepository $exchangeRepository, UserRepository $userRepository, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('auth_login');
        }

        $userCards = $user->getPokedex();
        $sender = $exchange->getSender();

        if ($this->array_containsall($exchange->getGainCards()->toArray(), $userCards->toArray())) {
            foreach ($exchange->getGainCards() as $card) {
                $user->removePokedex($card);
                $sender->addPokedex($card);
            }

            foreach ($exchange->getGivenCards() as $card) {
                $user->addPokedex($card);
            }

            $userRepository->save($user);
            $userRepository->save($sender);
            $exchange->setExecutedAt(new DateTime("now"));
            $exchange->setReceiver($user);
            $exchangeRepository->save($exchange);

            $status = "Exchange done!";
        } else {
            $status = "You don't have all the cards to accept this exchange!";
        }

        return $this->render('exchange/details/index.html.twig', [
            'controller_name' => 'ExchangeController',
            'exchange' => $exchange,
            "status" => $status,
        ]);
    }

    #[Route('/exchange-create', name: 'app_exchange_create')]
    public function create(Request $request, ExchangeRepository $exchangeRepository, PokemonCardRepository $pokemonCardRepository, UserRepository $userRepository): Response
    {
        $allCards = $pokemonCardRepository->findAll();

        if ($request->isMethod('POST')) {
            $givenCards = $request->request->all("givenCards");
            $gainCards = $request->request->all("gainCards");

            /** @var User $user */
            $user = $this->getUser();

            if (!$user) {
                return $this->redirectToRoute('auth_login');
            }

            if (!$givenCards || !$gainCards) return $this->redirect("/exchange-create");

            if ($this->array_containsall($givenCards, array_map(function ($c) {
                return $c->getId();
            }, $user->getPokedex()->toArray()))) {
                $ex = new Exchange();
                $ex->setSender($this->getUser());

                foreach ($givenCards as $cardId) {
                    $givethis = $pokemonCardRepository->findOneBy(array("id" => $cardId));
                    $ex->addGivenCard($givethis);
                    $user->removePokedex($givethis);
                }

                foreach ($gainCards as $cardId) {
                    $ex->addGainCard($pokemonCardRepository->findOneBy(array("id" => $cardId)));
                }

                $newID = $exchangeRepository->save($ex);
                $userRepository->save($user);

                return $this->redirect("/exchange/" . $newID);
            } else {
                return $this->redirect("/exchange-create");
            }

            return $this->redirect("/exchange");
        } else {
            return $this->render('exchange/create/index.html.twig', [
                "user" => $this->getUser(),
                "allCards" => $allCards,
            ]);
        }
    }
}
