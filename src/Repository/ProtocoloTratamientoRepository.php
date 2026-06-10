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

    /**
     * @return ProtocoloTratamiento[]
     */
    public function findByTreatmentName(string $treatmentName): array
    {
        $needle = mb_strtolower(trim($treatmentName));
        if ($needle === '') {
            return [];
        }

        return $this->createQueryBuilder('p')
            ->addSelect('m')
            ->innerJoin('p.material', 'm')
            ->andWhere('LOWER(TRIM(p.nombre_tratamiento)) = :name')
            ->setParameter('name', $needle)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
