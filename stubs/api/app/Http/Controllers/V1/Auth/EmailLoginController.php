<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Auth;

use Illuminate\Contracts\Support\Responsable;
use Thuraaung\ApiHelpers\Http\Responses\TokenResponse;
use App\Http\Requests\EmailLoginRequest;
use App\Commands\Auth\EmailLogin;

final readonly class EmailLoginController
{
    public function __construct(
        private EmailLogin $command,
    ) {
    }

    public function __invoke(EmailLoginRequest $request): Responsable
    {
        return new TokenResponse(
            token: $this->command->handle(
                credentials: $request->payload(),
            ),
        );
    }
}
