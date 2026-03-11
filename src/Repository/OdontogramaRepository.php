<?php

namespace App\Repository;

use App\Entity\Odontograma;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Odontograma>
 *
 * @method Odontograma|null find($id, $lockMode = null, $lockVersion = null)
 * @method Odontograma|null findOneBy(array $criteria, array $orderBy = null)
 * @method Odontograma[]    findAll()
 * @method Odontograma[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OdontogramaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Odontograma::class);
    }

//    /**
//     * @return Odontograma[] Returns an array of Odontograma objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Odontograma
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
