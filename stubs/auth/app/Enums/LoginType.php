<?php

declare(strict_types=1);

namespace App\Enums;

use function array_map;

enum LoginType: string
{
    case GOOGLE = 'google';

    case FACEBOOK = 'facebook';

    public static function values(): array
    {
        return array_map(
            callback: static fn (LoginType $loginType) => $loginType->value,
            array: self::cases(),
        );
    }

    public function match(string $value): bool
    {
        return $this->value === $value;
    }
}
