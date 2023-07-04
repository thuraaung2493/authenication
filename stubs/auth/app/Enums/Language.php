<?php

declare(strict_types=1);

namespace App\Enums;

use function array_map;

enum Language: string
{
    case MM = 'mm';

    case EN = 'en';

    public static function values(): array
    {
        return array_map(
            callback: static fn (Language $language) => $language->value,
            array: self::cases(),
        );
    }
}
