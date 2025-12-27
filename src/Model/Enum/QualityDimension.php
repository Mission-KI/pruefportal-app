<?php
declare(strict_types=1);

namespace App\Model\Enum;

/**
 * Quality Dimension Enum
 *
 * Represents the six quality dimensions in the MISSION KI quality standard:
 * DA (Datenqualität), ND (Nachhaltigkeit & Diversität), TR (Transparenz),
 * MA (Mensch-KI-Interaktion), VE (Verantwortung), CY (Cybersecurity)
 *
 * Provides metadata (IDs, icons, i18n labels) for quality dimension rendering.
 */
enum QualityDimension: string
{
    case DA = 'DA';
    case ND = 'ND';
    case TR = 'TR';
    case MA = 'MA';
    case VE = 'VE';
    case CY = 'CY';

    public function id(): int
    {
        return match ($this) {
            self::DA => 10,
            self::ND => 20,
            self::TR => 30,
            self::MA => 40,
            self::VE => 50,
            self::CY => 60,
        };
    }

    /**
     * Get icon identifier for this quality dimension.
     *
     * Returns the filename (without .svg extension) for use with the icon atom:
     * $this->element('atoms/icon', ['name' => $qualityDimension->icon()])
     *
     * Icon files are located in webroot/icons/
     *
     * @return string Icon filename identifier
     */
    public function icon(): string
    {
        return match ($this) {
            self::DA => 'data-quality',
            self::ND => 'non-discrimination',
            self::TR => 'transparency',
            self::MA => 'human-oversight',
            self::VE => 'ai-security',
            self::CY => 'reliability',
        };
    }

    public function label(string $lang = 'de'): string
    {
        return match ($lang) {
            'en' => $this->labelEn(),
            default => $this->labelDe(),
        };
    }

    private function labelDe(): string
    {
        return match ($this) {
            self::DA => 'Datenqualität',
            self::ND => 'Nachhaltigkeit & Diversität',
            self::TR => 'Transparenz',
            self::MA => 'Mensch-KI-Interaktion',
            self::VE => 'Verantwortung',
            self::CY => 'Cybersecurity',
        };
    }

    private function labelEn(): string
    {
        return match ($this) {
            self::DA => 'Data Quality',
            self::ND => 'Sustainability & Diversity',
            self::TR => 'Transparency',
            self::MA => 'Human-AI Interaction',
            self::VE => 'Responsibility',
            self::CY => 'Cybersecurity',
        };
    }

    public static function codes(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public static function tryFromValue(string $value): ?self
    {
        return self::tryFrom(strtoupper($value));
    }
}
