<?php
declare(strict_types=1);

namespace App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;

/**
 * Salutation Enum
 */
enum Salutation: string implements EnumLabelInterface
{
    use EnumOptionsTrait;

    case Ms = 'ms';
    case Mr = 'mr';
    case Diverse = 'diverse';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Ms => 'Frau',
            self::Mr => 'Herr',
            self::Diverse => 'Divers',
        };
    }
}
