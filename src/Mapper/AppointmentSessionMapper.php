<?php

namespace App\Mapper;

use App\Dto\AppointmentSessionMaterialLineDTO;
use App\Dto\AppointmentSessionResponseDTO;
use App\Entity\Appointment;
use App\Entity\AppointmentSession;
use App\Entity\AppointmentSessionMaterial;

class AppointmentSessionMapper
{
    public function toResponseDto(AppointmentSession $session, Appointment $appointment): AppointmentSessionResponseDTO
    {
        $dto = new AppointmentSessionResponseDTO();
        $dto->id = $session->getId();
        $dto->appointment_id = $appointment->getId();
        $dto->status = $session->getStatus();
        $dto->started_at = $session->getStartedAt()?->format(\DateTimeInterface::ATOM);
        $dto->completed_at = $session->getCompletedAt()?->format(\DateTimeInterface::ATOM);
        $dto->notes = $session->getNotes();
        $dto->treatment_name = $this->resolveTreatmentName($appointment);
        $dto->materials = array_map(
            fn (AppointmentSessionMaterial $line) => $this->materialLineToDto($line),
            $session->getMaterials()->toArray()
        );

        return $dto;
    }

    private function materialLineToDto(AppointmentSessionMaterial $line): AppointmentSessionMaterialLineDTO
    {
        $material = $line->getMaterial();
        $dto = new AppointmentSessionMaterialLineDTO();
        $dto->id = $line->getId();
        $dto->stock_material_id = $material?->getId();
        $dto->material_nombre = $material?->getNombre();
        $dto->unidad = $material?->getUnidad();
        $dto->cantidad_planificada = $line->getCantidadPlanificada();
        $dto->cantidad_usada = $line->getCantidadUsada();
        $dto->stock_disponible = $material?->getCantidadActual() ?? 0.0;
        $dto->from_protocol = $line->isFromProtocol();
        $dto->protocol_notes = $line->getProtocolNotes();

        return $dto;
    }

    private function resolveTreatmentName(Appointment $appointment): ?string
    {
        $catalog = $appointment->getCatalogTreatment();
        if ($catalog !== null && $catalog->getName() !== null && trim($catalog->getName()) !== '') {
            return trim($catalog->getName());
        }

        $text = $appointment->getTreatment();

        return $text !== null && trim($text) !== '' ? trim($text) : null;
    }
}
