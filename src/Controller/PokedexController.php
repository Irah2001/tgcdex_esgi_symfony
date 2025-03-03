<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PokemonCardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PokedexController extends AbstractController
{
    #[Route('/pokedex', name: 'pokedex')]
    #[IsGranted('ROLE_USER')]
    public function index(PokemonCardRepository $pokemonCardRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException("Utilisateur non authentifié.");
        }

        $ownedCards = $user->getPokedex();
        $notOwnedCards = $pokemonCardRepository->findNotOwnedByUser($user->getId());

        return $this->render('pokedex/index.html.twig', [
            'ownedCards' => $ownedCards,
            'notOwnedCards' => $notOwnedCards,
        ]);
    }
}
