<?php

namespace App\Service;

use App\Repository\PokemonCardRepository;

class BoosterService {

    public function generateBoosterCards(PokemonCardRepository $pokemonCardRepository): array
    {
        // This is a placeholder. In a real application, you'd fetch this from a database or API
        $cards = $pokemonCardRepository->findAll();
        
        // Shuffle the cards to randomize the order
        shuffle($cards);
        
        // Return only the first 10 cards
        return array_slice($cards, 0, 10);
    }
}