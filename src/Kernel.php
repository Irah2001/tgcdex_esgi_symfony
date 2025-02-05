<?php

namespace App;

use App\Entity\PokemonCard;
use Pokedex;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    function boot(): void
    {
        parent::boot();
        set_time_limit(300);
        ini_set('memory_limit',' 2048M');
        
        $output = new ConsoleOutput();

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $cr = $entityManager->getRepository(PokemonCard::class);
        if (count($cr->findAll()) != 0){
            $output->write("Database not empty! Skipping.");
        } else {

            $pkdex = new Pokedex();
            $tgcDex = $pkdex->getDex("fr");
    
            $sets = $tgcDex->set->list();
            if (count($sets) == 0) {
                $output->write("No sets available - is the API online ?");
            } 
    
            $amount = 0;
            
            $setBar = new ProgressBar($output, count($sets));
            $setBar->start();
            foreach ($sets as $j => $set) {
                $cardSet = $tgcDex->set->get($set->id);
                foreach ($cardSet->cards as $i => $card) {
                    $c = new PokemonCard();
                    if (empty($card->image)) continue;
                    
                    $c->setImgUrl($card->image."/high.png");
                    $c->setName($card->name);
    
                    $cr->save($c, $j == array_key_last($sets) && $i == array_key_last($cardSet->cards));
                    $amount++;
                }
                $setBar->advance();
            }
            $setBar->finish();
        }
    }
}
