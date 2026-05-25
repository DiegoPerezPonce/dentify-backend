<?php

namespace App\Repository;

use App\Entity\Box;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Box>
 */
class BoxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Box::class);
    }

    public function existsByNombre(string $nombre, ?int $excludeId = null): bool
    {
        $normalized = mb_strtolower(trim($nombre));

        $qb = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('LOWER(b.nombre) = :nombre')
            ->setParameter('nombre', $normalized);

        if ($excludeId !== null) {
            $qb->andWhere('b.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
