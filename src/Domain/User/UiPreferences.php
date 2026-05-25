<?php

namespace App\Domain\User;

/**
 * Preferencias de interfaz por usuario (odontólogo / recepción / admin).
 *
 * @phpstan-type UiPreferencesArray array{
 *   scheme?: string,
 *   primary?: string,
 *   surface?: string,
 *   preset?: string,
 *   menu_mode?: string
 * }
 */
final class UiPreferences
{
    public const SCHEME_LIGHT = 'light';
    public const SCHEME_DARK = 'dark';

    public const MENU_STATIC = 'static';
    public const MENU_OVERLAY = 'overlay';

    /** @var list<string> */
    public const SCHEMES = [self::SCHEME_LIGHT, self::SCHEME_DARK];

    /** @var list<string> */
    public const PRIMARY_KEYS = [
        'cyan', 'teal', 'emerald', 'lime', 'orange', 'amber', 'yellow',
        'sky', 'indigo', 'violet', 'purple', 'pink', 'red',
    ];

    /** @var list<string> */
    public const SURFACE_KEYS = [
        'slate', 'zinc', 'neutral', 'stone', 'gray',
        'clinical', 'navy', 'ocean', 'forest', 'plum',
        'arctic', 'mint', 'denim', 'sage', 'mauve', 'taupe',
        'sand', 'burgundy',
    ];

    /** @var list<string> */
    public const PRESET_KEYS = ['aura', 'lara', 'nora'];

    /** @var list<string> */
    public const MENU_MODES = [self::MENU_STATIC, self::MENU_OVERLAY];

    public function __construct(
        public readonly string $scheme = self::SCHEME_LIGHT,
        public readonly string $primary = 'cyan',
        public readonly string $surface = 'slate',
        public readonly string $preset = 'aura',
        public readonly string $menuMode = self::MENU_STATIC,
    ) {}

    /** @param array<string, mixed>|null $raw */
    public static function fromArray(?array $raw): self
    {
        if ($raw === null || $raw === []) {
            return new self();
        }

        return new self(
            self::normalizeScheme($raw['scheme'] ?? null),
            self::normalizePrimary($raw['primary'] ?? null),
            self::normalizeSurface($raw['surface'] ?? null),
            self::normalizePreset($raw['preset'] ?? null),
            self::normalizeMenuMode($raw['menu_mode'] ?? $raw['menuMode'] ?? null),
        );
    }

    /** @return array{scheme: string, primary: string, surface: string, preset: string, menu_mode: string} */
    public function toArray(): array
    {
        return [
            'scheme' => $this->scheme,
            'primary' => $this->primary,
            'surface' => $this->surface,
            'preset' => $this->preset,
            'menu_mode' => $this->menuMode,
        ];
    }

    private static function normalizeScheme(mixed $v): string
    {
        $s = \is_string($v) ? strtolower(trim($v)) : '';

        return \in_array($s, self::SCHEMES, true) ? $s : self::SCHEME_LIGHT;
    }

    private static function normalizePrimary(mixed $v): string
    {
        $s = \is_string($v) ? strtolower(trim($v)) : '';

        return \in_array($s, self::PRIMARY_KEYS, true) ? $s : 'cyan';
    }

    private static function normalizeSurface(mixed $v): string
    {
        $s = \is_string($v) ? strtolower(trim($v)) : '';

        return \in_array($s, self::SURFACE_KEYS, true) ? $s : 'slate';
    }

    private static function normalizePreset(mixed $v): string
    {
        $s = \is_string($v) ? strtolower(trim($v)) : '';

        return \in_array($s, self::PRESET_KEYS, true) ? $s : 'aura';
    }

    private static function normalizeMenuMode(mixed $v): string
    {
        $s = \is_string($v) ? strtolower(trim($v)) : '';

        return \in_array($s, self::MENU_MODES, true) ? $s : self::MENU_STATIC;
    }
}
