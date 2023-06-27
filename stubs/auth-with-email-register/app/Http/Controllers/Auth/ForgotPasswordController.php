<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Commands\Auth\ResendOtp;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Contracts\Support\Responsable;
use Thuraaung\ApiHelpers\Http\Responses\MessageResponse;

use function strval;
use function trans;

final readonly class ForgotPasswordController
{
    public function __construct(
        private ResendOtp $command,
    ) {
    }

    public function __invoke(ForgotPasswordRequest $request): Responsable
    {
        $this->command->handle(
            email: $request->string('email')->toString(),
        );

        return new MessageResponse(
            message: strval(trans('auth.forgot_password')),
        );
    }
}
