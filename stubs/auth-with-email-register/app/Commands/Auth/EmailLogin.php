<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\DataObjects\Auth\EmailLoginCredentials;
use App\Exceptions\Auth\EmailLoginException;
use App\Queries\Users\FetchGmailUser;
use Laravel\Sanctum\NewAccessToken;
use Thuraaung\ApiHelpers\Http\Enums\Status;

use function trans;

final readonly class EmailLogin
{
    public function __construct(
        private FetchGmailUser $query,
    ) {
    }

    public function handle(EmailLoginCredentials $credentials): NewAccessToken
    {
        $user = $this->query->handle($credentials->email);

        if ( ! $user || ! Hash::check($credentials->password, $user->password)) {
            throw new EmailLoginException(
                message: trans('auth.login_failed'),
                code: Status::UNAUTHORIZED->value,
            );
        }

        if (null === $user->email_verified_at) {
            throw new EmailLoginException(
                message: trans('auth.email_not_verified'),
                code: Status::UNAUTHORIZED->value,
            );
        }

        return $user->createToken($credentials->email);
    }
}
