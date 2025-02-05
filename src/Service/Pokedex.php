<?php

namespace App\Service;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use TCGdex\TCGdex;
use Symfony\Component\HttpClient\Psr18Client;

/**
 * Used to get the pokedex to do requests
 */
class Pokedex {
    /**
     * Get TCG Dex using language
     */
    public function getDex($lang = "fr") : TCGdex {
        TCGdex::$requestFactory = new Psr17Factory();
        TCGdex::$responseFactory = new Psr17Factory();

        TCGdex::$client = new Psr18Client();

        return new TCGdex($lang);
    }
}
?>