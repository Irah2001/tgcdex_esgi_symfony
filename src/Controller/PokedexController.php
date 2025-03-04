<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PokemonCardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PokedexController extends AbstractController
{
    #[Route('/pokedex', name: 'pokedex')]
    public function index(Request $request, PokemonCardRepository $pokemonCardRepository): RedirectResponse | Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            $targetUrl = $request->getUri();

            return new RedirectResponse('/login?redirect=' . urlencode($targetUrl));
        }

        $ownedCards = $user->getPokedex();
        $notOwnedCards = $pokemonCardRepository->findNotOwnedByUser($user->getId());

        return $this->render('pokedex/index.html.twig', [
            'ownedCards' => $ownedCards,
            'notOwnedCards' => $notOwnedCards,
        ]);
    }
}
