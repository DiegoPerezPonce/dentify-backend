<?php

namespace App\Repository;

use App\Entity\Radiografia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Radiografia>
 *
 * @method Radiografia|null find($id, $lockMode = null, $lockVersion = null)
 * @method Radiografia|null findOneBy(array $criteria, array $orderBy = null)
 * @method Radiografia[]    findAll()
 * @method Radiografia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RadiografiaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Radiografia::class);
    }
}
