<?php

namespace App\Mapper;

use App\Dto\PatientCreateDTO;
use App\Dto\PatientResponseDTO;
use App\Dto\PatientUpdateDTO;
use App\Entity\Patient;

class PatientMapper
{
    public function toEntity(PatientCreateDTO $dto): Patient
    {
        $patient = new Patient();
        $patient->setNombre($dto->nombre);
        $patient->setApellidos($dto->apellidos);
        $patient->setDni($dto->dni);
        $patient->setNss($dto->nss);
        $patient->setTelefono($dto->telefono);
        $patient->setEmail($dto->email);
        $patient->setEnfermedades($dto->enfermedades);
        $patient->setAlergias($dto->alergias);
        $patient->setHistorialClinico($dto->historial_clinico);
        $patient->setDatosFacturacion($dto->datos_facturacion);
        $patient->setMedicalFlags($dto->medical_flags ?? null);
        $patient->setMedicalNotes($dto->medical_notes);
        $patient->setTerminosGeneralesAceptados($dto->terminos_generales_aceptados);

        return $patient;
    }

    public function updateEntity(Patient $patient, PatientUpdateDTO $dto): Patient
    {
        if ($dto->nombre !== null) {
            $patient->setNombre($dto->nombre);
        }
        if ($dto->apellidos !== null) {
            $patient->setApellidos($dto->apellidos);
        }
        if ($dto->dni !== null) {
            $patient->setDni($dto->dni);
        }
        if ($dto->nss !== null) {
            $patient->setNss($dto->nss);
        }
        if ($dto->telefono !== null) {
            $patient->setTelefono($dto->telefono);
        }
        if ($dto->email !== null) {
            $patient->setEmail($dto->email);
        }
        if ($dto->enfermedades !== null) {
            $patient->setEnfermedades($dto->enfermedades);
        }
        if ($dto->alergias !== null) {
            $patient->setAlergias($dto->alergias);
        }
        if ($dto->historial_clinico !== null) {
            $patient->setHistorialClinico($dto->historial_clinico);
        }
        if ($dto->datos_facturacion !== null) {
            $patient->setDatosFacturacion($dto->datos_facturacion);
        }
        if ($dto->medical_flags !== null) {
            $patient->setMedicalFlags($dto->medical_flags);
        }
        if ($dto->medical_notes !== null) {
            $patient->setMedicalNotes($dto->medical_notes);
        }
        if ($dto->terminos_generales_aceptados !== null) {
            $patient->setTerminosGeneralesAceptados($dto->terminos_generales_aceptados);
        }

        return $patient;
    }

    public function toResponseDto(Patient $patient): PatientResponseDTO
    {
        $dto = new PatientResponseDTO();
        $dto->id = $patient->getId();
        $dto->nombre = $patient->getNombre();
        $dto->apellidos = $patient->getApellidos();
        $dto->dni = $patient->getDni();
        $dto->nss = $patient->getNss();
        $dto->telefono = $patient->getTelefono();
        $dto->email = $patient->getEmail();
        $dto->enfermedades = $patient->getEnfermedades();
        $dto->alergias = $patient->getAlergias();
        $dto->historial_clinico = $patient->getHistorialClinico();
        $dto->datos_facturacion = $patient->getDatosFacturacion();
        $dto->medical_flags = $patient->getMedicalFlags();
        $dto->medical_notes = $patient->getMedicalNotes();
        $dto->terminos_generales_aceptados = $patient->isTerminosGeneralesAceptados();
        $dto->terminos_pendientes = $patient->hasTerminosPendientes();

        return $dto;
    }
}
