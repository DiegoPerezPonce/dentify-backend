<?php

namespace App\Mapper;

use App\Dto\TreatmentCreateDTO;
use App\Dto\TreatmentResponseDTO;
use App\Entity\Treatment;
use App\Repository\SpecialtyRepository;
use App\Repository\TreatmentCategoryRepository;

class TreatmentMapper
{
    public function __construct(
        private TreatmentCategoryRepository $categoryRepository,
        private SpecialtyRepository $specialtyRepository
    ) {}

    public function toEntity(TreatmentCreateDTO $dto, bool $isCustom = true): Treatment
    {
        $category = $this->categoryRepository->find($dto->categoryId);
        if ($category === null) {
            throw new \InvalidArgumentException('Treatment category not found.');
        }
        $dentistSpecialty = $this->specialtyRepository->find($dto->dentistSpecialtyId);
        if ($dentistSpecialty === null) {
            throw new \InvalidArgumentException('Dentist specialty not found.');
        }

        $treatment = new Treatment();
        $treatment->setCategory($category);
        $treatment->setDentistSpecialty($dentistSpecialty);
        $treatment->setName(trim((string) $dto->name));
        $treatment->setDefaultDurationMinutes($dto->defaultDurationMinutes);
        $treatment->setAllowsUrgency($dto->allowsUrgency);
        $treatment->setForFirstVisit($dto->forFirstVisit);
        $treatment->setIsCustom($isCustom);
        $treatment->setActive(true);

        return $treatment;
    }

    public function toResponseDto(Treatment $treatment): TreatmentResponseDTO
    {
        $dto = new TreatmentResponseDTO();
        $dto->id = $treatment->getId();
        $dto->name = $treatment->getName();
        $dto->categoryId = $treatment->getCategory()?->getId();
        $dto->categoryName = $treatment->getCategory()?->getName();
        $dto->dentistSpecialtyId = $treatment->getDentistSpecialty()?->getId();
        $dto->dentistSpecialtyName = $treatment->getDentistSpecialty()?->getName();
        $dto->specialtyId = $dto->dentistSpecialtyId;
        $dto->specialtyName = $dto->dentistSpecialtyName;
        $dto->defaultDurationMinutes = $treatment->getDefaultDurationMinutes();
        $dto->allowsUrgency = $treatment->isAllowsUrgency();
        $dto->forFirstVisit = $treatment->isForFirstVisit();
        $dto->isCustom = $treatment->isCustom();
        $dto->active = $treatment->isActive();

        return $dto;
    }
}
