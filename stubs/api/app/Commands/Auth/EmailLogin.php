<?php

declare(strict_types=1);

namespace Thuraaung\Authentication\Commands;

use App\Models\User;
use Laravel\Sanctum\NewAccessToken;
use Thuraaung\Authentication\DataObjects\EmailLoginCredentials;
use Thuraaung\Authentication\Enums\LoginType;
use Thuraaung\Authentication\Exceptions\EmailLoginException;

final readonly class EmailLogin
{
    public function handle(EmailLoginCredentials $credentials): NewAccessToken
    {
        /** @var ?User $user */
        $user = User::query()
            ->where('login_type', LoginType::GMAIL->value)
            ->where('email', $credentials->email)
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
