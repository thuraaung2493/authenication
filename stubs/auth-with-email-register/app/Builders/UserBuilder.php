<?php

declare(strict_types=1);

namespace App\Builders;

use App\Enums\LoginType;
use Illuminate\Database\Eloquent\Builder;

final class UserBuilder extends Builder
{
    public function whereSocialLogin(LoginType $type, ?string $id): static
    {
        $this->where('login_type', $type->value)->where('login_id', $id);

        return $this;
    }

    public function whereGmailLogin(string $email): static
    {
        $this->where('login_type', LoginType::GMAIL->value)->where('email', $email);

        return $this;
    }
}
