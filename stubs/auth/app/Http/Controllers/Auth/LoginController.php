<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Commands\Auth\SocialLogin;
use Illuminate\Contracts\Support\Responsable;
use Thuraaung\ApiHelpers\Http\Responses\TokenResponse;
use App\Http\Requests\LoginRequest;

final readonly class LoginController
{
    public function __construct(
        private SocialLogin $socialLogin,
    ) {
    }

    public function __invoke(LoginRequest $request): Responsable
    {
        $token = $this->socialLogin->handle($request->payload());

        return new TokenResponse(
            token: $token,
        );
    }
}
