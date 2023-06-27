<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use Illuminate\Contracts\Auth\Factory;

final readonly class Logout
{
    public function __construct(
        private Factory $factory,
    ) {
    }

    public function handle(): bool
    {
        $user =  $this->factory->guard()->user();

        return $user->currentAccessToken()->delete();
    }
}
