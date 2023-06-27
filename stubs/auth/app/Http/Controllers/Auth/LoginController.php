<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Commands\Auth\EmailLogin;
use App\Commands\Auth\SocialLogin;
use App\Enums\LoginType;
use Illuminate\Contracts\Support\Responsable;
use Thuraaung\ApiHelpers\Http\Responses\TokenResponse;
use App\Http\Requests\LoginRequest;

final readonly class LoginController
{
    public function __construct(
        private EmailLogin $emailLogin,
        private SocialLogin $socialLogin,
    ) {
    }

    public function __invoke(LoginRequest $request, string $type): Responsable
    {
        if (LoginType::GMAIL->match($type)) {
            $token = $this->emailLogin->handle($request->payload());
        } else {
            $token = $this->socialLogin->handle($request->payload());
        }

        return new TokenResponse(
            token: $token,
        );
    }
}
