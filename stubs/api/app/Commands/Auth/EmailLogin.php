<?php

declare(strict_types=1);

namespace Thuraaung\Authentication\Commands;

use App\DataObjects\Auth\EmailLoginCredentials;
use App\Exceptions\EmailLoginException;
use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

final readonly class EmailLogin
{
    public function handle(EmailLoginCredentials $credentials): NewAccessToken
    {
        /** @var ?User $user */
        $user = User::query()
            ->whereGmailLogin($credentials->email)
            ->first();

        if ( ! $user || ! Hash::check($credentials->password, $user->password)) {
            throw new EmailLoginException('User credentials did not match!');
        }

        if (null === $user->email_verified_at) {
            throw new EmailLoginException('Your email is not verified yet!');
        }

        return $user->createToken($credentials->email);
    }
}
