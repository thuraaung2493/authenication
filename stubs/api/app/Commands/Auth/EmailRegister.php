<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Commands\Otps\CreateOtp;
use App\Commands\Otps\SendOtp;
use App\DataObjects\Auth\EmailRegisterData;
use App\Models\User;

final readonly class EmailRegister
{
    public function __construct(
        private CreateOtp $createOtp,
        private SendOtp $sendOtp,
    ) {
    }

    public function handle(EmailRegisterData $data): void
    {
        $user = User::query()->create(
            attributes: $data->toArray(),
        );

        $otp = $this->createOtp->handle(
            email: $data->email,
        );

        $this->sendOtp->handle(
            user: $user,
            otp: $otp
        );
    }
}
