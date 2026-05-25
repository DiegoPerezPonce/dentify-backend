<?php

namespace App\Repository;

use App\Entity\Dentist;
use App\Entity\DentistWeeklySchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DentistWeeklySchedule>
 */
class DentistWeeklyScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DentistWeeklySchedule::class);
    }

    /**
     * @return DentistWeeklySchedule[]
     */
    public function findByDentist(Dentist $dentist): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.dentist = :dentist')
            ->setParameter('dentist', $dentist)
            ->orderBy('s.weekday', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
