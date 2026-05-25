<?php

namespace App\Repository;

use App\Domain\Appointment\AppointmentKindCatalogFilter;
use App\Entity\Treatment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Treatment>
 */
class TreatmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Treatment::class);
    }

    /**
     * @return Treatment[]
     */
    public function findForAppointmentFilters(
        AppointmentKindCatalogFilter $filter,
        ?int $categoryId = null
    ): array {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.category', 'c')
            ->innerJoin('t.dentistSpecialty', 'ds')
            ->addSelect('c', 'ds')
            ->andWhere('t.active = true')
            ->andWhere('c.active = true')
            ->andWhere('ds.active = true')
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('t.name', 'ASC');

        if ($categoryId !== null) {
            $qb->andWhere('c.id = :cid')->setParameter('cid', $categoryId);
        }

        $categoryNames = $filter->categoryNames;
        if ($categoryNames !== null && $categoryNames !== []) {
            $qb->andWhere('c.name IN (:cnames)')->setParameter('cnames', $categoryNames);
        }

        if ($filter->urgentOnly === true) {
            $qb->andWhere('t.allowsUrgency = true');
        }

        if ($filter->excludeFirstVisitOnly === true) {
            $qb->andWhere('t.forFirstVisit = false');
        }

        return $qb->getQuery()->getResult();
    }
}
