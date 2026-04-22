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
     * @param int $bufferMinutes Optional buffer time in minutes to check around the range.
     * @return Appointment[]
     */
    public function findOverlaps($dentist, $box, \DateTimeInterface $fecha, \DateTimeInterface $startTime, int $durationMinutes, ?int $excludeId = null, int $bufferMinutes = 0): array
    {
        $newStart = clone $startTime;
        /** @var \DateTime $newEnd */
        $newEnd = clone $startTime;
        $newEnd->modify("+$durationMinutes minutes");

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

        return array_filter($appointments, function (Appointment $appointment) use ($newStart, $newEnd, $bufferMinutes) {
            $existingStart = $appointment->getHoraInicio();
            /** @var \DateTime $existingEnd */
            $existingEnd = clone $existingStart;
            $existingEnd->modify("+{$appointment->getDuracion()} minutes");

            // Overlap condition with buffer: (StartA < EndB + buffer) AND (EndA > StartB - buffer)
            // This ensures at least $bufferMinutes gap between appointments.
            /** @var \DateTime $bufferedExistingEnd */
            $bufferedExistingEnd = clone $existingEnd;
            $bufferedExistingEnd->modify("+$bufferMinutes minutes");
            
            /** @var \DateTime $bufferedExistingStart */
            $bufferedExistingStart = clone $existingStart;
            $bufferedExistingStart->modify("-$bufferMinutes minutes");

            return ($newStart < $bufferedExistingEnd) && ($newEnd > $bufferedExistingStart);
        });
    }

    /**
     * Finds appointments scheduled later in the same day for a specific dentist or box.
     *
     * @param \App\Entity\Dentist|null $dentist
     * @param \App\Entity\Box|null $box
     * @param \DateTimeInterface $fecha
     * @param \DateTimeInterface $startTime
     * @param int|null $excludeId
     * @return Appointment[]
     */
    public function findLaterAppointmentsSameDay($dentist, $box, \DateTimeInterface $fecha, \DateTimeInterface $startTime, ?int $excludeId = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.fecha = :fecha')
            ->andWhere('a.hora_inicio > :startTime')
            ->setParameter('fecha', $fecha->format('Y-m-d'))
            ->setParameter('startTime', $startTime->format('H:i:s'));

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

        return $qb->getQuery()->getResult();
    }
}
