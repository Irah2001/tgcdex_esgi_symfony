<?php

namespace App\Controller;

use App\Service\BoosterService;
use App\Repository\PokemonCardRepository;
use App\Repository\UserRepository;
use App\Entity\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BoosterController extends AbstractController
{
    #[Route('/booster', name: 'booster')]
    public function booster(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            $targetUrl = $request->getUri();

            return new RedirectResponse('/login?redirect=' . urlencode($targetUrl));
        }

        return $this->render('booster/index.html.twig', []);
    }

    #[Route('/booster/open', name: 'open_booster')]
    public function openBooster(Request $request, BoosterService $boosterService, PokemonCardRepository $pokemonCardRepository, UserRepository $userRepository): RedirectResponse | Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            $targetUrl = $request->getUri();

            return new RedirectResponse('/login?redirect=' . urlencode($targetUrl));
        }
        
        $cards = $boosterService->generateBoosterCards($pokemonCardRepository);

        foreach ($cards as $card) {
            $user->addPokedex($card);
        }
        
        $userRepository->save($user);

        return $this->render('booster/open.html.twig', [
            'cards' => $cards,
        ]);
    }
}