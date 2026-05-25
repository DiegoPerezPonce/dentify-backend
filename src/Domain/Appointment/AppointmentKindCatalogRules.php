<?php

namespace App\Domain\Appointment;

/**
 * Qué parte del catálogo clínico es visible según el tipo de cita.
 *
 * - primera_visita → solo categoría Revisión dental
 * - urgencia → procedimientos con allows_urgency en cualquier categoría
 * - tratamiento → resto clínico (excluye procedimientos marcados solo para primera visita)
 */
final class AppointmentKindCatalogRules
{
    private const REVISION_DENTAL = 'Revisión dental';

    public static function filterFor(?string $appointmentKind): AppointmentKindCatalogFilter
    {
        if ($appointmentKind === null || !AppointmentKind::isValid($appointmentKind)) {
            return new AppointmentKindCatalogFilter();
        }

        return match ($appointmentKind) {
            AppointmentKind::PRIMERA_VISITA => new AppointmentKindCatalogFilter(
                categoryNames: [self::REVISION_DENTAL],
            ),
            AppointmentKind::URGENCIA => new AppointmentKindCatalogFilter(
                urgentOnly: true,
            ),
            AppointmentKind::TRATAMIENTO => new AppointmentKindCatalogFilter(
                excludeFirstVisitOnly: true,
            ),
            default => new AppointmentKindCatalogFilter(),
        };
    }
}
