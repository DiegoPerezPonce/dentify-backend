<?php

namespace App\Repository;

use App\Entity\ProtocoloTratamiento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProtocoloTratamiento>
 */
class ProtocoloTratamientoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProtocoloTratamiento::class);
    }
}
