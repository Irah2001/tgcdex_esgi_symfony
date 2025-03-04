<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PokemonCardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PokedexController extends AbstractController
{
    #[Route('/pokedex', name: 'pokedex')]
    public function index(PokemonCardRepository $pokemonCardRepository): RedirectResponse | Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('auth_login');
        }

        $ownedCards = $user->getPokedex();
        $notOwnedCards = $pokemonCardRepository->findNotOwnedByUser($user->getId());

        return $this->render('pokedex/index.html.twig', [
            'ownedCards' => $ownedCards,
            'notOwnedCards' => $notOwnedCards,
        ]);
    }
}
