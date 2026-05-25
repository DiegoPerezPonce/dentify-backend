<?php

namespace App\Service;

use App\Entity\Appointment;
use App\Repository\ClinicScheduleSettingsRepository;

final class ClinicHoursValidator
{
    public function __construct(
        private ClinicScheduleSettingsRepository $clinicSettingsRepository
    ) {}

    public function validateAppointment(Appointment $appointment): ?string
    {
        $fecha = $appointment->getFecha();
        $hora = $appointment->getHoraInicio();
        $duracion = $appointment->getDuracion();

        if ($fecha === null || $hora === null || $duracion === null || $duracion < 1) {
            return null;
        }

        $start = \DateTime::createFromInterface($fecha);
        $start->setTime(
            (int) $hora->format('H'),
            (int) $hora->format('i'),
            0
        );

        return $this->validateWindow($start, $duracion);
    }

    public function validateWindow(\DateTimeInterface $start, int $durationMinutes): ?string
    {
        $settings = $this->clinicSettingsRepository->getOrCreateDefault();
        $open = $this->timeToMinutes($settings->getOpenTime());
        $close = $this->timeToMinutes($settings->getCloseTime());
        $startMinutes = (int) $start->format('H') * 60 + (int) $start->format('i');
        $endMinutes = $startMinutes + $durationMinutes;

        if ($startMinutes < $open || $endMinutes > $close) {
            return sprintf(
                'La cita debe estar dentro del horario de la clínica (%s – %s).',
                $settings->getOpenTime(),
                $settings->getCloseTime()
            );
        }

        return null;
    }

    private function timeToMinutes(string $hhmm): int
    {
        $parts = explode(':', trim($hhmm));

        return ((int) ($parts[0] ?? 0)) * 60 + (int) ($parts[1] ?? 0);
    }
}
