<?php

declare(strict_types=1);

namespace Thuraaung\Authenication\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Thuraaung\ApiHelpers\Http\Responses\TokenResponse;
use Thuraaung\Authenication\Http\Requests\SocialLoginRequest;

final class SocialLoginController
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
