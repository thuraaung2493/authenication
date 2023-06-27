<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Commands\Auth\ResendOtp;
use App\Http\Requests\Auth\OtpResendRequest;
use Illuminate\Contracts\Support\Responsable;
use Thuraaung\ApiHelpers\Http\Responses\MessageResponse;

use function trans;
use function strval;

final class OtpResendController
{
    public function __construct(
        private ResendOtp $resendOtp,
    ) {
    }

    public function __invoke(OtpResendRequest $request): Responsable
    {
        $this->resendOtp->handle(
            email: $request->string('email')->toString()
        );

        return new MessageResponse(
            message: strval(trans('auth.otp_resend')),
        );
    }
}
