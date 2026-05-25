<?php

namespace App\Domain\Appointment;

/** Filtros de catálogo derivados de `appointment_kind`. */
final class AppointmentKindCatalogFilter
{
    /**
     * @param list<string>|null $categoryNames Si se define, solo esas categorías clínicas.
     */
    public function __construct(
        public readonly ?array $categoryNames = null,
        public readonly ?bool $urgentOnly = null,
        public readonly ?bool $excludeFirstVisitOnly = null,
    ) {}
}
