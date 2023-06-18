<?php

declare(strict_types=1);

namespace App\Commands\Otps;

use App\Models\Otp;
use Thuraaung\OtpGenerator\Facades\OtpGenerator;

final readonly class CreateOtp
{
    public function handle(string $email): Otp
    {
        return Otp::query()->updateOrCreate(
            attributes: [
                'email' => $email,
            ],
            values: [
                'otp' => OtpGenerator::generate(),
                'expired_at' => now()->addMinute(),
            ],
        );
    }
}
