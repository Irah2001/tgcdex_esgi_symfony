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
            return $this->redirectToRoute('auth_login');
        }

        $ownedPage = max(1, (int) $request->query->get('owned_page', 1));
        $notOwnedPage = max(1, (int) $request->query->get('not_owned_page', 1));
        $limit = 25;

        $ownedData = $pokemonCardRepository->findOwnedByUserPaginated($user->getId(), $ownedPage, $limit);
        $notOwnedData = $pokemonCardRepository->findNotOwnedByUserPaginated($user->getId(), $notOwnedPage, $limit);

        return $this->render('pokedex/index.html.twig', [
            'ownedCards' => $ownedData['items'],
            'ownedTotalPages' => $ownedData['pages'],
            'ownedCurrentPage' => $ownedPage,
            'notOwnedCards' => $notOwnedData['items'],
            'notOwnedTotalPages' => $notOwnedData['pages'],
            'notOwnedCurrentPage' => $notOwnedPage,
        ]);
    }
}
