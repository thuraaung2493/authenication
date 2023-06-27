<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Models\User;

final readonly class ResendOtp
{
    public function __construct(
        private CreateOtp $createOtp,
        private SendOtp $sendOtp,
    ) {
    }

    public function handle(string $email): void
    {
        /** @var User */
        $user = User::query()->whereGmailLogin($email)->first();

        $otp = $this->createOtp->handle(
            email: $email,
        );

        $this->sendOtp->handle(
            user: $user,
            otp: $otp,
        );
    }
}
