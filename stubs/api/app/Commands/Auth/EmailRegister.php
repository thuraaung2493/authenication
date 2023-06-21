<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Commands\Otps\CreateOtp;
use App\Commands\Otps\SendOtp;
use App\DataObjects\Auth\EmailRegisterInfo;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class EmailRegister
{
    public function __construct(
        private CreateOtp $createOtp,
        private SendOtp $sendOtp,
    ) {
    }

    public function handle(EmailRegisterInfo $data): void
    {
        DB::transaction(function () use ($data): void {

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
        });
    }
}
