<?php

namespace App\Mapper;

use App\Domain\User\UiPreferences;
use App\Dto\UserUiPreferencesDTO;
use App\Dto\UserUiPreferencesUpdateDTO;
use App\Entity\User;

class UiPreferencesMapper
{
    public function toDto(UiPreferences $prefs): UserUiPreferencesDTO
    {
        $dto = new UserUiPreferencesDTO();
        $dto->scheme = $prefs->scheme;
        $dto->primary = $prefs->primary;
        $dto->surface = $prefs->surface;
        $dto->preset = $prefs->preset;
        $dto->menu_mode = $prefs->menuMode;

        return $dto;
    }

    public function fromUser(User $user): UserUiPreferencesDTO
    {
        return $this->toDto($user->getUiPreferences());
    }

    public function applyUpdate(User $user, UserUiPreferencesUpdateDTO $patch): UiPreferences
    {
        $current = $user->getUiPreferences();

        $merged = UiPreferences::fromArray([
            'scheme' => $patch->scheme ?? $current->scheme,
            'primary' => $patch->primary ?? $current->primary,
            'surface' => $patch->surface ?? $current->surface,
            'preset' => $patch->preset ?? $current->preset,
            'menu_mode' => $patch->menu_mode ?? $current->menuMode,
        ]);

        $user->setUiPreferences($merged);

        return $merged;
    }
}
