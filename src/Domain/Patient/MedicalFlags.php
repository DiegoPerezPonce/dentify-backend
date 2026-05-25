<?php

declare(strict_types=1);

namespace App\Domain\Patient;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Etiquetas permitidas en Patient.medical_flags (API + persistencia).
 * Tres niveles: bioseguridad (1), alergias críticas (2), riesgo sistémico (3).
 *
 * Condiciones personalizadas: prefijo X{1|2|3}: + etiqueta en UTF-8 codificada en base64url
 * (ej. X1:... para infecciosas). Misma gravedad visual que el nivel indicado.
 */
final class MedicalFlags
{
    /** Longitud máxima del texto legible antes de codificar (UTF-8). */
    public const CUSTOM_LABEL_MAX_LENGTH = 120;

    /** Longitud máxima del string guardado en JSON. */
    public const CUSTOM_FLAG_MAX_BYTES = 512;

    // —— Nivel 1: bioseguridad / infecciosas ——
    public const VIH = 'VIH';

    public const HEPATITIS_B = 'Hepatitis_B';

    public const HEPATITIS_C = 'Hepatitis_C';

    public const TUBERCULOSIS = 'Tuberculosis';

    public const SIFILIS = 'Sifilis';

    public const HERPES_ACTIVO = 'Herpes_activo';

    // —— Nivel 2: alergias críticas ——
    public const ALLERGY_PENICILLIN = 'Alergia_Penicilina';

    public const ALLERGY_LATEX = 'Alergia_Latex';

    public const ALLERGY_LOCAL_ANESTHETICS = 'Alergia_anestesicos_locales';

    public const ALLERGY_METALS = 'Alergia_metales_niquel_cobalto';

    public const ALLERGY_NSAIDS = 'Alergia_aines';

    // —— Nivel 3: riesgo sistémico / medicación ——
    public const BISPHOSPHONATES = 'Bifosfonatos';

    public const ANTICOAGULANTS = 'Anticoagulados';

    public const HEART_DISEASE = 'Cardiopatias';

    public const DIABETES = 'Diabetes';

    public const EPILEPSY = 'Epilepsia';

    /** @var list<string> Nivel 1 — alerta roja / bioseguridad en UI. */
    public const LEVEL1_BIOSECURITY = [
        self::VIH,
        self::HEPATITIS_B,
        self::HEPATITIS_C,
        self::TUBERCULOSIS,
        self::SIFILIS,
        self::HERPES_ACTIVO,
    ];

    /**
     * @deprecated Usar LEVEL1_BIOSECURITY
     *
     * @var list<string>
     */
    public const INFECTIOUS = self::LEVEL1_BIOSECURITY;

    /** @return list<string> */
    public static function allowed(): array
    {
        return [
            ...self::LEVEL1_BIOSECURITY,
            self::ALLERGY_PENICILLIN,
            self::ALLERGY_LATEX,
            self::ALLERGY_LOCAL_ANESTHETICS,
            self::ALLERGY_METALS,
            self::ALLERGY_NSAIDS,
            self::BISPHOSPHONATES,
            self::ANTICOAGULANTS,
            self::HEART_DISEASE,
            self::DIABETES,
            self::EPILEPSY,
        ];
    }

    public static function isValidMedicalFlag(string $flag): bool
    {
        if (in_array($flag, self::allowed(), true)) {
            return true;
        }

        return null !== self::tryDecodeCustomFlag($flag);
    }

    /**
     * @return array{level: int, label: string}|null level 1..3
     */
    public static function tryDecodeCustomFlag(string $flag): ?array
    {
        if (strlen($flag) > self::CUSTOM_FLAG_MAX_BYTES) {
            return null;
        }
        if (!preg_match('/^X([123]):([A-Za-z0-9_-]+)$/', $flag, $m)) {
            return null;
        }
        $level = (int) $m[1];
        $b64 = strtr($m[2], '-_', '+/');
        $pad = strlen($b64) % 4;
        if (0 !== $pad) {
            $b64 .= str_repeat('=', 4 - $pad);
        }
        $raw = base64_decode($b64, true);
        if (false === $raw) {
            return null;
        }
        if (!mb_check_encoding($raw, 'UTF-8')) {
            return null;
        }
        $label = trim($raw);
        if ('' === $label) {
            return null;
        }
        if (mb_strlen($label) > self::CUSTOM_LABEL_MAX_LENGTH) {
            return null;
        }
        if (str_contains($label, "\0")) {
            return null;
        }

        return ['level' => $level, 'label' => $label];
    }

    public static function encodeCustomFlag(int $level, string $label): ?string
    {
        if ($level < 1 || $level > 3) {
            return null;
        }
        $trim = trim($label);
        if ('' === $trim) {
            return null;
        }
        if (mb_strlen($trim) > self::CUSTOM_LABEL_MAX_LENGTH) {
            return null;
        }
        $enc = rtrim(strtr(base64_encode($trim), '+/', '-_'), '=');
        $out = 'X'.$level.':'.$enc;
        if (strlen($out) > self::CUSTOM_FLAG_MAX_BYTES) {
            return null;
        }

        return $out;
    }

    public static function validateMedicalFlagElement(mixed $value, ExecutionContextInterface $context): void
    {
        if (!is_string($value)) {
            return;
        }
        if (self::isValidMedicalFlag($value)) {
            return;
        }
        $context->buildViolation('Marcador médico no válido.')
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
