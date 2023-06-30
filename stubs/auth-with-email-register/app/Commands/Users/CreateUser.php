<?php

declare(strict_types=1);

namespace App\Commands\Users;

use App\DataObjects\Auth\EmailRegisterInfo;
use App\DataObjects\Auth\SocialLoginInfo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Thuraaung\SpaceStorage\Facades\SpaceStorage;

use function strval;

final class CreateUser
{
    public function handle(SocialLoginInfo|EmailRegisterInfo $data): User
    {
        $profile = $this->createProfile($data->profile);

        return User::query()->create([
            ...$data->toArray(),
            'profile' => $profile
        ]);
    }

    private function createProfile(string|UploadedFile|null $profile): ?string
    {
        if (null === $profile) {
            return null;
        }

        return SpaceStorage::upload(
            folder: strval(config('folders.profiles')),
            file: $profile,
        );
    }
}
