<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\AppointmentSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppointmentSession>
 */
class AppointmentSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppointmentSession::class);
    }

    public function findOneByAppointment(Appointment|int $appointment): ?AppointmentSession
    {
        $id = $appointment instanceof Appointment ? $appointment->getId() : $appointment;

        return $this->createQueryBuilder('s')
            ->addSelect('m', 'mat')
            ->leftJoin('s.materials', 'm')
            ->leftJoin('m.material', 'mat')
            ->andWhere('s.appointment = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
