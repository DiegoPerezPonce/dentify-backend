<?php

namespace App\Mapper;

use App\Dto\DentistCreateDTO;
use App\Dto\DentistResponseDTO;
use App\Dto\DentistUpdateDTO;
use App\Entity\Dentist;

class DentistMapper
{
    public function toEntity(DentistCreateDTO $dto): Dentist
    {
        $dentist = new Dentist();
        $dentist->setNombre($dto->nombre);
        $dentist->setApellidos($dto->apellidos);
        $dentist->setEspecialidad($dto->especialidad);
        $dentist->setEmail($dto->email);

        return $dentist;
    }

    public function updateEntity(Dentist $dentist, DentistUpdateDTO $dto): Dentist
    {
        if ($dto->nombre !== null) {
            $dentist->setNombre($dto->nombre);
        }
        if ($dto->apellidos !== null) {
            $dentist->setApellidos($dto->apellidos);
        }
        if ($dto->especialidad !== null) {
            $dentist->setEspecialidad($dto->especialidad);
        }
        if ($dto->email !== null) {
            $dentist->setEmail($dto->email);
        }

        return $dentist;
    }

    public function toResponseDto(Dentist $dentist): DentistResponseDTO
    {
        $dto = new DentistResponseDTO();
        $dto->id = $dentist->getId();
        $dto->nombre = $dentist->getNombre();
        $dto->apellidos = $dentist->getApellidos();
        $dto->especialidad = $dentist->getEspecialidad();
        $dto->email = $dentist->getEmail();

        return $dto;
    }
}
