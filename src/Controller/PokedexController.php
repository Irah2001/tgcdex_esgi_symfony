<?php

namespace App\Controller;

use App\Repository\PokemonCardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

final class PokedexController extends AbstractController
{
    #[Route('/pokedex', name: 'pokedex')]
    public function index(
        PokemonCardRepository $pokemonCardRepository,
        PaginatorInterface $paginator,
        Request $request
    ): RedirectResponse | Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('auth_login');
        }

        $ownedArray = $user->getPokedex();
        $ownedCards = $paginator->paginate(
            $ownedArray,
            $request->query->getInt('page', 1),
            10
        );

        $notOwnedQuery = $pokemonCardRepository->findNotOwnedByUser($user->getId());
        $notOwnedCards = $paginator->paginate(
            $notOwnedQuery,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pokedex/index.html.twig', [
            'ownedCards' => $ownedCards,
            'notOwnedCards' => $notOwnedCards,
        ]);
    }
}
