<?php

namespace App\Mapper;

use App\Dto\TreatmentCategoryGroupDTO;
use App\Entity\Treatment;
use App\Entity\TreatmentCategory;

class TreatmentCategoryMapper
{
    public function __construct(
        private TreatmentMapper $treatmentMapper
    ) {}

    /**
     * @param TreatmentCategory[] $categories
     * @param Treatment[]         $treatments  flat list (already filtered)
     *
     * @return TreatmentCategoryGroupDTO[]
     */
    public function toGroupedDtos(array $categories, array $treatments): array
    {
        $byCategory = [];
        foreach ($treatments as $treatment) {
            $cid = $treatment->getCategory()?->getId();
            if ($cid === null) {
                continue;
            }
            $byCategory[$cid][] = $treatment;
        }

        $result = [];
        foreach ($categories as $category) {
            $cid = $category->getId();
            $items = $byCategory[$cid] ?? [];
            if ($items === []) {
                continue;
            }
            $dto = new TreatmentCategoryGroupDTO();
            $dto->id = $cid;
            $dto->name = $category->getName();
            $dto->sortOrder = $category->getSortOrder();
            $dto->treatments = array_map(
                fn (Treatment $t) => $this->treatmentMapper->toResponseDto($t),
                $items
            );
            $result[] = $dto;
        }

        return $result;
    }
}
