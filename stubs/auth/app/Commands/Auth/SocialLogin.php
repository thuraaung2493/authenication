<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Commands\Users\CreateUser;
use App\Commands\Users\UpdateUser;
use App\DataObjects\Auth\SocialLoginInfo;
use App\Queries\Users\FetchUserBySocialType;
use Laravel\Sanctum\NewAccessToken;

final readonly class SocialLogin
{
    public function __construct(
        private FetchUserBySocialType $fetchUserBySocialType,
        private CreateUser $createUser,
        private UpdateUser $updateUser,
    ) {
    }

    public function handle(SocialLoginInfo $data): NewAccessToken
    {
        $user = $this->fetchUserBySocialType->handle($data->loginType, $data->loginId);

        if ($user) {
            $this->updateUser->handle($user, $data);
        } else {
            $user = $this->createUser->handle($data);
        }

        return $user->createToken(strval($data->loginId));
    }
}
