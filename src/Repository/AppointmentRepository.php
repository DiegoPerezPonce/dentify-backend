<?php

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appointment>
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    /**
     * Finds appointments that overlap with the given time range for a specific dentist or box.
     *
     * @param \App\Entity\Dentist|null $dentist
     * @param \App\Entity\Box|null $box
     * @param \DateTimeInterface $fecha
     * @param \DateTimeInterface $startTime
     * @param int $durationMinutes
     * @param int|null $excludeId
     * @return Appointment[]
     */
    public function findOverlaps($dentist, $box, \DateTimeInterface $fecha, \DateTimeInterface $startTime, int $durationMinutes, ?int $excludeId = null): array
    {
        $newStart = clone $startTime;
        $newEnd = (clone $startTime)->modify("+$durationMinutes minutes");

        $qb = $this->createQueryBuilder('a')
            ->where('a.fecha = :fecha')
            ->setParameter('fecha', $fecha->format('Y-m-d'));

        $orX = $qb->expr()->orX();
        if ($dentist) {
            $orX->add('a.odontologo = :dentist');
            $qb->setParameter('dentist', $dentist);
        }
        if ($box) {
            $orX->add('a.box = :box');
            $qb->setParameter('box', $box);
        }
        $qb->andWhere($orX);

        if ($excludeId) {
            $qb->andWhere('a.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        /** @var Appointment[] $appointments */
        $appointments = $qb->getQuery()->getResult();

        return array_filter($appointments, function (Appointment $appointment) use ($newStart, $newEnd) {
            $existingStart = $appointment->getHoraInicio();
            $existingEnd = (clone $existingStart)->modify("+{$appointment->getDuracion()} minutes");

            // Overlap condition: (StartA < EndB) AND (EndA > StartB)
            return ($newStart < $existingEnd) && ($newEnd > $existingStart);
        });
    }
}
