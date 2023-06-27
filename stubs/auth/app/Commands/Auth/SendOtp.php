<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Mail\SendOtpCode;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

final class SendOtp
{
    public function handle(Otp $otp, ?User $user = null): void
    {
        Mail::to(
            users: $user ?? $otp->emaill,
        )->queue(
            mailable: new SendOtpCode(
                user: $user,
                otp: $otp,
            ),
        );
    }
}
