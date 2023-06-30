<?php

declare(strict_types=1);

namespace App\Queries\Users;

use App\Enums\LoginType;
use App\Models\User;

final readonly class FetchUserBySocialType
{
    public function handle(LoginType $loginType, string $loginId): User|null
    {
        return User::query()
            ->whereLoginType($loginType->value)
            ->whereLoginId($loginId)
            ->first();
    }
}
