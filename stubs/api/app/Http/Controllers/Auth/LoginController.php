<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Commands\Auth\SocialLogin;
use Illuminate\Contracts\Support\Responsable;
use Thuraaung\ApiHelpers\Http\Responses\TokenResponse;
use App\Http\Requests\SocialLoginRequest;

final class LoginController
{
    public function __construct(
        private readonly SocialLogin $socialLogin,
    ) {
    }

    public function __invoke(SocialLoginRequest $request): Responsable
    {
        return new TokenResponse(
            token: $this->socialLogin->handle(
                data: $request->payload()
            ),
        );
    }
}
