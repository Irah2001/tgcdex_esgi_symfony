<?php

namespace App\Service;

use App\Repository\PokemonCardRepository;

class BoosterService
{

    public function generateBoosterCards(PokemonCardRepository $pokemonCardRepository): array
    {
        $cards = $pokemonCardRepository->findAll();

        shuffle($cards);

        return array_slice($cards, 0, 10);
    }
}
