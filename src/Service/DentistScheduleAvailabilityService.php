<?php

namespace App\Service;

use App\Entity\Appointment;
use App\Entity\ClinicScheduleSettings;
use App\Entity\Dentist;
use App\Entity\DentistWeeklySchedule;
use App\Repository\AppointmentRepository;
use App\Repository\BoxRepository;
use App\Repository\ClinicScheduleSettingsRepository;
use App\Repository\DentistRepository;
use App\Repository\DentistWeeklyScheduleRepository;

final class DentistScheduleAvailabilityService
{
    public function __construct(
        private DentistRepository $dentistRepository,
        private DentistWeeklyScheduleRepository $weeklyScheduleRepository,
        private ClinicScheduleSettingsRepository $clinicSettingsRepository,
        private AppointmentRepository $appointmentRepository,
        private BoxRepository $boxRepository
    ) {}

    /**
     * @return int[] IDs de odontólogos con turno asignado y sin solape de cita (incl. margen 5 min).
     */
    public function findAvailableDentistIds(
        \DateTimeInterface $start,
        int $durationMinutes,
        ?int $excludeAppointmentId = null
    ): array {
        if ($durationMinutes < 1) {
            return [];
        }

        $settings = $this->clinicSettingsRepository->getOrCreateDefault();
        $weekday = $this->scheduleWeekdayFromDate($start);
        if ($weekday === null) {
            return [];
        }

        $fecha = \DateTime::createFromInterface($start)->setTime(0, 0, 0);
        $horaInicio = \DateTime::createFromInterface($start);
        $horaInicio->setDate((int) $fecha->format('Y'), (int) $fecha->format('m'), (int) $fecha->format('d'));

        $startMinutes = $this->timeToMinutes($horaInicio);
        $endMinutes = $startMinutes + $durationMinutes;

        $available = [];
        foreach ($this->dentistRepository->findAllWithLinkedUser() as $dentist) {
            $dentistId = $dentist->getId();
            if ($dentistId === null) {
                continue;
            }
            if (!$this->isWithinAssignedShift($dentist, $weekday, $startMinutes, $endMinutes, $settings)) {
                continue;
            }
            $overlaps = $this->appointmentRepository->findOverlaps(
                $dentist,
                null,
                $fecha,
                $horaInicio,
                $durationMinutes,
                $excludeAppointmentId,
                5
            );
            if ($overlaps !== []) {
                continue;
            }
            $available[] = $dentistId;
        }

        return $available;
    }

    /**
     * @return int[] IDs de boxes disponibles (estado disponible y sin cita solapada).
     */
    public function findAvailableBoxIds(
        \DateTimeInterface $start,
        int $durationMinutes,
        ?int $excludeAppointmentId = null
    ): array {
        if ($durationMinutes < 1) {
            return [];
        }

        $fecha = \DateTime::createFromInterface($start)->setTime(0, 0, 0);
        $horaInicio = \DateTime::createFromInterface($start);
        $horaInicio->setDate((int) $fecha->format('Y'), (int) $fecha->format('m'), (int) $fecha->format('d'));

        $available = [];
        foreach ($this->boxRepository->findAll() as $box) {
            $boxId = $box->getId();
            if ($boxId === null) {
                continue;
            }
            if (strtolower((string) $box->getEstado()) !== 'disponible') {
                continue;
            }
            $overlaps = $this->appointmentRepository->findOverlaps(
                null,
                $box,
                $fecha,
                $horaInicio,
                $durationMinutes,
                $excludeAppointmentId,
                0
            );
            if ($overlaps === []) {
                $available[] = $boxId;
            }
        }

        return $available;
    }

    private function isWithinAssignedShift(
        Dentist $dentist,
        int $weekday,
        int $startMinutes,
        int $endMinutes,
        ClinicScheduleSettings $settings
    ): bool {
        $shift = $this->resolveShiftForWeekday($dentist, $weekday);
        if ($shift === null || $shift === 'off') {
            return false;
        }

        $range = ScheduleTimeResolver::rangeForShift($shift, $settings);
        if ($range === null) {
            return false;
        }

        $shiftStart = $this->timeToMinutes($range['start']);
        $shiftEnd = $this->timeToMinutes($range['end']);

        return $startMinutes >= $shiftStart && $endMinutes <= $shiftEnd;
    }

    private function resolveShiftForWeekday(Dentist $dentist, int $weekday): ?string
    {
        $rows = $this->weeklyScheduleRepository->findByDentist($dentist);
        foreach ($rows as $row) {
            if ($row->getWeekday() === $weekday) {
                return $row->getShift();
            }
        }

        return 'off';
    }

    /** 1 = lunes … 6 = sábado; domingo → null. */
    private function scheduleWeekdayFromDate(\DateTimeInterface $date): ?int
    {
        $iso = (int) $date->format('N');
        if ($iso === 7) {
            return null;
        }

        return $iso;
    }

    private function timeToMinutes(\DateTimeInterface|string $time): int
    {
        if ($time instanceof \DateTimeInterface) {
            return (int) $time->format('H') * 60 + (int) $time->format('i');
        }
        $parts = explode(':', trim($time));

        return ((int) ($parts[0] ?? 0)) * 60 + (int) ($parts[1] ?? 0);
    }
}
