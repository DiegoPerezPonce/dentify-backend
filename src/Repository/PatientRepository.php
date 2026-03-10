<?php

namespace App\Repository;

use App\Entity\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Patient>
 */
class PatientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patient::class);
    }

    /**
     * @return Patient[]
     */
    public function findBySearchTerm(string $term): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.dni LIKE :term')
            ->orWhere('p.nombre LIKE :term')
            ->orWhere('p.apellidos LIKE :term')
            ->orWhere('p.email LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->getQuery()
            ->getResult();
    }
}
