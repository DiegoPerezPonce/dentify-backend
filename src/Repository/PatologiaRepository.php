<?php

namespace App\Repository;

use App\Entity\Patologia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Patologia>
 *
 * @method Patologia|null find($id, $lockMode = null, $lockVersion = null)
 * @method Patologia|null findOneBy(array $criteria, array $orderBy = null)
 * @method Patologia[]    findAll()
 * @method Patologia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PatologiaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patologia::class);
    }
}
