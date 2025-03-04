<?php

namespace App\Repository;

use App\Entity\PokemonCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

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

    public function save(PokemonCard $card, $flush = true)
    {
        $this->entityManager->persist($card);
        if ($flush) $this->entityManager->flush();
    }

    /**
     * Get all cards owned by a user.
     */
    public function findOwnedByUserPaginated(int $userId, int $page = 1, int $limit = 25): array
    {
        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.users', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('p.name', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query);

        return [
            'items' => $paginator->getIterator(),
            'total' => $paginator->count(),
            'pages' => ceil($paginator->count() / $limit)
        ];
    }

    /**
     * Get all cards owned by a user.
     */
    public function findNotOwnedByUserPaginated(int $userId, int $page = 1, int $limit = 25): array
    {
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.users', 'u', 'WITH', 'u.id = :userId')
            ->where('u.id IS NULL')
            ->setParameter('userId', $userId)
            ->orderBy('p.name', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query);

        return [
            'items' => $paginator->getIterator(),
            'total' => $paginator->count(),
            'pages' => ceil($paginator->count() / $limit)
        ];
    }
}
