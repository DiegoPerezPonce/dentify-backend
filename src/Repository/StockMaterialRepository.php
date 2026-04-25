<?php

namespace App\Repository;

use App\Entity\StockMaterial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StockMaterial>
 */
class StockMaterialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockMaterial::class);
    }

    /**
     * @param array{
     *   search?: string,
     *   lowStockOnly?: bool
     * } $filters
     *
     * @return array{0: StockMaterial[], 1: int}
     */
    public function findList(array $filters, ?int $offset, ?int $limit): array
    {
        $countQb = $this->createQueryBuilder('m');
        $this->applyListFilters($countQb, $filters);
        $countQb->select('COUNT(m.id)');
        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        $qb = $this->createQueryBuilder('m');
        $this->applyListFilters($qb, $filters);
        $qb->orderBy('m.nombre', 'ASC');

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }
        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        /** @var StockMaterial[] $items */
        $items = $qb->getQuery()->getResult();

        return [$items, $total];
    }

    /**
     * @param array{search?: string, lowStockOnly?: bool} $filters
     */
    private function applyListFilters(QueryBuilder $qb, array $filters): void
    {
        if (!empty($filters['search'])) {
            $qb->andWhere('LOWER(m.nombre) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($filters['search']) . '%');
        }
        if (!empty($filters['lowStockOnly'])) {
            $qb->andWhere('m.cantidad_actual <= m.umbral_minimo');
        }
    }
}
