<?php

namespace App\Repository;

use App\Entity\TreatmentCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TreatmentCategory>
 */
class TreatmentCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TreatmentCategory::class);
    }

    /**
     * @return TreatmentCategory[]
     */
    public function findAllActiveOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.active = true')
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
