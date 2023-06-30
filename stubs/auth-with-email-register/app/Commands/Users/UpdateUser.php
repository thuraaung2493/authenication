<?php

declare(strict_types=1);

namespace App\Commands\Users;

use App\DataObjects\Auth\SocialLoginInfo;
use App\Models\User;
use Thuraaung\SpaceStorage\Facades\SpaceStorage;

use function strval;

final class UpdateUser
{
    public function handle(User $user, SocialLoginInfo $data): bool
    {
        $this->updateProfile($user, $data->profile);

        return $user->update($data->toArray());
    }

    private function updateProfile(User $user, string|null $profile): bool
    {
        if (null === $profile) {
            return false;
        }

        return SpaceStorage::update(
            oldPath: strval($user->getRawOriginal('profile')),
            file: $profile
        );
    }
}
