<?php

declare(strict_types=1);

namespace App\Queries\Users;

use App\Enums\LoginType;
use App\Models\User;

final readonly class FetchGmailUser
{
    public function handle(string $email): User|null
    {
        return User::query()
            ->whereLoginType(LoginType::GMAIL->value)
            ->whereEmail($email)
            ->first();
    }
}
