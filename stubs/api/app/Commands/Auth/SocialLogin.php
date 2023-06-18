<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\DataObjects\Auth\SocialLoginInfo;
use App\Models\User;
use Laravel\Sanctum\NewAccessToken;
use Thuraaung\SpaceStorage\Facades\SpaceStorage;

final readonly class SocialLogin
{
    public function handle(SocialLoginInfo $data): NewAccessToken
    {
        /** @var ?User $user */
        $user = User::query()->whereSocialLogin(
            type: $data->loginType,
            id: $data->loginId
        )->first();

        if ($user) {
            $this->updateUser($user, $data);
        } else {
            $user = $this->createUser($data);
        }

        return $user->createToken(strval($data->loginId));
    }

    private function createUser(SocialLoginInfo $data): User
    {
        $profile = $this->createProfile($data->profile);

        return User::query()->create([
            ...$data->toArray(),
            'profile' => $profile
        ]);
    }

    private function updateUser(User $user, SocialLoginInfo  $data): void
    {
        $user->update($data->toArray());

        $this->updateProfile($user, $data->profile);
    }

    private function createProfile(string|null $profile): ?string
    {
        if (null === $profile) {
            return null;
        }

        return $this->fileStorage->upload(
            folder: \strval(\config('folders.profiles')),
            file: $profile,
        );
    }

    private function updateProfile(User $user, string|null $profile): bool
    {
        if (null === $profile) {
            return false;
        }

        return SpaceStorage::update(
            oldPath: \strval($user->getRawOriginal('profile')),
            file: $profile
        );
    }
}
