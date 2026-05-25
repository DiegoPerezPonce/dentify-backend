<?php

namespace App\Repository;

use App\Entity\Dentist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dentist>
 */
class DentistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dentist::class);
    }

    public function findOneByEmailIgnoreCase(string $email): ?Dentist
    {
        $email = trim($email);
        if ($email === '') {
            return null;
        }

        return $this->createQueryBuilder('d')
            ->where('LOWER(d.email) = :email')
            ->setParameter('email', mb_strtolower($email))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Odontólogos con cuenta de usuario vinculada (fichas activas del sistema).
     *
     * @return Dentist[]
     */
    public function findAllWithLinkedUser(): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('EXISTS (SELECT 1 FROM App\Entity\User u WHERE u.dentist = d)')
            ->orderBy('d.apellidos', 'ASC')
            ->addOrderBy('d.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
