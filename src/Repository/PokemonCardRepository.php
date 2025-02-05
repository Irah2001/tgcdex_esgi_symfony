<?php

namespace App\Repository;

use App\Entity\PokemonCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PokemonCard>
 */
class PokemonCardRepository extends ServiceEntityRepository
{

    private $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        $this->entityManager = $registry->getManager();
        parent::__construct($registry, PokemonCard::class);
    }

    public function save(PokemonCard $card, $flush = true){
        $this->entityManager->persist($card);
        if ($flush) $this->entityManager->flush();
    }

//    /**
//     * @return PokemonCard[] Returns an array of PokemonCard objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PokemonCard
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
