<?php

namespace App\Mapper;

use App\Dto\BoxCreateDTO;
use App\Dto\BoxResponseDTO;
use App\Dto\BoxUpdateDTO;
use App\Entity\Box;
use App\Repository\DentistRepository;

class BoxMapper
{
    public function __construct(
        private DentistRepository $dentistRepository
    ) {}

    public function toEntity(BoxCreateDTO $dto): Box
    {
        $box = new Box();
        $box->setNombre($dto->nombre);
        $box->setDescripcion($dto->descripcion);
        $box->setEstado($dto->estado);

        if ($dto->dentist_id !== null) {
            $dentist = $this->dentistRepository->find($dto->dentist_id);
            if (!$dentist) {
                throw new \InvalidArgumentException('Dentist not found with ID: ' . $dto->dentist_id);
            }
            $box->setDentist($dentist);
        }

        return $box;
    }

    public function updateEntity(Box $box, BoxUpdateDTO $dto): Box
    {
        if ($dto->nombre !== null) {
            $box->setNombre($dto->nombre);
        }
        if ($dto->descripcion !== null) {
            $box->setDescripcion($dto->descripcion);
        }
        if ($dto->estado !== null) {
            $box->setEstado($dto->estado);
        }

        if ($dto->clear_dentist) {
            $box->setDentist(null);
        } elseif ($dto->dentist_id !== null) {
            $dentist = $this->dentistRepository->find($dto->dentist_id);
            if (!$dentist) {
                throw new \InvalidArgumentException('Dentist not found with ID: ' . $dto->dentist_id);
            }
            $box->setDentist($dentist);
        }

        return $box;
    }

    public function toResponseDto(Box $box): BoxResponseDTO
    {
        $dto = new BoxResponseDTO();
        $dto->id = $box->getId();
        $dto->nombre = $box->getNombre();
        $dto->descripcion = $box->getDescripcion();
        $dto->estado = $box->getEstado();
        $dto->dentistId = $box->getDentist()?->getId();
        $dto->dentistNombre = $box->getDentist() ? $box->getDentist()->getNombre() . ' ' . $box->getDentist()->getApellidos() : null;

        return $dto;
    }
}
