<?php

namespace App\Controller;

use App\Entity\Exchange;
use App\Entity\PokemonCard;
use App\Entity\User;
use App\Repository\ExchangeRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExchangeController extends AbstractController
{
    #[Route('/exchange', name: 'app_exchange')]
    public function index(ExchangeRepository $exchangeRepository): Response
    {
        $exchanges = $exchangeRepository->findAll();
        $exchanges = array_filter($exchanges, function ($ex){
            return $ex->getExecutedAt() == null;
        });

        $ex1 = new Exchange();
        $c1 = new PokemonCard();
        $c1->setName("TEST");
        $c1->setImgUrl("https://assets.tcgdex.net/fr/base/base1/72/high.png");
        $c2 = new PokemonCard();
        $c2->setName("TEST2");
        $c2->setImgUrl("https://assets.tcgdex.net/fr/base/base2/20/high.png");
        $ex1->addGainCard($c1);
        $ex1->addGivenCard($c2);
        $sen1 = new User();
        $sen1->setEmail("a@b.c");
        $ex1->setSender($sen1);

        $exem = array($ex1, $ex1, $ex1, $ex1, $ex1, $ex1, $ex1, $ex1, $ex1);

        return $this->render('exchange/index.html.twig', [
            'controller_name' => 'ExchangeController',
            'exchanges' => $exem,
        ]);
    }

    #[Route('/exchange/{id}', name: 'app_exchange_details')]
    public function details(?Exchange $exchange) : Response {

        $ex1 = new Exchange();
        $c1 = new PokemonCard();
        $c1->setName("TEST");
        $c1->setImgUrl("https://assets.tcgdex.net/fr/base/base1/72/high.png");
        $c2 = new PokemonCard();
        $c2->setName("TEST2");
        $c2->setImgUrl("https://assets.tcgdex.net/fr/base/base2/20/high.png");
        $ex1->addGainCard($c1);
        $ex1->addGainCard($c2);
        $ex1->addGivenCard($c1);
        $ex1->addGivenCard($c2);
        $sen1 = new User();
        $sen1->setEmail("a@b.c");
        $ex1->setSender($sen1);

        //if (!$exchange) return $this->redirectToRoute("app_exchange");
        return $this->render('exchange_details/index.html.twig', [
            'controller_name' => 'ExchangeController',
            'exchange' => $ex1,
        ]);
    }
}
