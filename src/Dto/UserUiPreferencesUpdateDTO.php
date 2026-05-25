<?php

namespace App\Dto;

use App\Domain\User\UiPreferences;
use Symfony\Component\Validator\Constraints as Assert;

class UserUiPreferencesUpdateDTO
{
    #[Assert\Choice(choices: UiPreferences::SCHEMES)]
    public ?string $scheme = null;

    #[Assert\Choice(choices: UiPreferences::PRIMARY_KEYS)]
    public ?string $primary = null;

    #[Assert\Choice(choices: UiPreferences::SURFACE_KEYS)]
    public ?string $surface = null;

    #[Assert\Choice(choices: UiPreferences::PRESET_KEYS)]
    public ?string $preset = null;

    #[Assert\Choice(choices: UiPreferences::MENU_MODES)]
    public ?string $menu_mode = null;
}
