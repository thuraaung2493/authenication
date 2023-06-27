<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Commands\Auth\VerifyEmail;
use App\Http\Requests\Auth\EmailVerifyRequest;
use Illuminate\Contracts\Support\Responsable;
use Thuraaung\ApiHelpers\Http\Responses\TokenResponse;

final class EmailVerifyController
{
    public function __construct(
        private VerifyEmail $command,
    ) {
    }

    public function __invoke(EmailVerifyRequest $request): Responsable
    {
        return new TokenResponse(
            token: $this->command->handle(
                data: $request->payload()
            ),
        );
    }
}
