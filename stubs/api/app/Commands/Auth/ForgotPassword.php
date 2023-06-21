<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Otps\CreateOtp;
use App\Commands\Otps\SendOtp;
use App\Models\User;

final readonly class ForgotPassword
{
    public function __construct(
        private CreateOtp $createOtp,
        private SendOtp $sendOtp,
    ) {
    }

    public function handle(string $email): bool
    {
        /** @var User */
        $user = User::query()->whereGmailLogin($email)->first();

        if (null === $user) {
            return false;
        }

        $otp = $this->createOtp->handle(
            email: $email,
        );

        $this->sendOtp->handle(
            user: $user,
            otp: $otp,
        );

        return true;
    }
}
