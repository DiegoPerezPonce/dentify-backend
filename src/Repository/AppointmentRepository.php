<?php

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * Lista citas con filtros opcionales (Issue #24: CRUD list para calendario / notificaciones).
     *
     * @param array{
     *   patientId?: int,
     *   dentistId?: int,
     *   boxId?: int,
     *   startDate?: \DateTimeInterface,
     *   endDate?: \DateTimeInterface,
     *   estado?: string
     * } $filters
     *
     * @return array{0: Appointment[], 1: int}
     */
    public function findList(array $filters, ?int $offset, ?int $limit): array
    {
        $countQb = $this->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.id)')
            ->innerJoin('a.paciente', 'p')
            ->innerJoin('a.odontologo', 'o')
            ->innerJoin('a.box', 'b');
        $this->applyListFilters($countQb, $filters);
        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        $qb = $this->createQueryBuilder('a')
            ->select('a', 'p', 'o', 'b')
            ->innerJoin('a.paciente', 'p')->addSelect('p')
            ->innerJoin('a.odontologo', 'o')->addSelect('o')
            ->innerJoin('a.box', 'b')->addSelect('b');
        $this->applyListFilters($qb, $filters);
        $qb->orderBy('a.fecha', 'ASC')
            ->addOrderBy('a.hora_inicio', 'ASC');

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }
        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        /** @var Appointment[] $items */
        $items = $qb->getQuery()->getResult();

        return [$items, $total];
    }

    /**
     * @param array{
     *   patientId?: int,
     *   dentistId?: int,
     *   boxId?: int,
     *   startDate?: \DateTimeInterface,
     *   endDate?: \DateTimeInterface,
     *   estado?: string
     * } $filters
     */
    private function applyListFilters(QueryBuilder $qb, array $filters): void
    {
        if (!empty($filters['patientId'])) {
            $qb->andWhere('p.id = :patientId')
                ->setParameter('patientId', $filters['patientId']);
        }
        if (!empty($filters['dentistId'])) {
            $qb->andWhere('o.id = :dentistId')
                ->setParameter('dentistId', $filters['dentistId']);
        }
        if (!empty($filters['boxId'])) {
            $qb->andWhere('b.id = :boxId')
                ->setParameter('boxId', $filters['boxId']);
        }
        if (!empty($filters['startDate'])) {
            $qb->andWhere('a.fecha >= :startDate')
                ->setParameter('startDate', $filters['startDate']->format('Y-m-d'));
        }
        if (!empty($filters['endDate'])) {
            $qb->andWhere('a.fecha <= :endDate')
                ->setParameter('endDate', $filters['endDate']->format('Y-m-d'));
        }
        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $qb->andWhere('a.estado = :estado')
                ->setParameter('estado', $filters['estado']);
        }
    }
}
