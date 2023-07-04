<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\DataObjects\Auth\EmailConfirmation;
use App\Exceptions\Auth\InvalidOtpException;
use App\Models\Otp;
use App\Models\User;
use Laravel\Sanctum\NewAccessToken;
use Thuraaung\ApiHelpers\Http\Enums\Status;

use function strval;
use function trans;

final readonly class VerifyEmail
{
    public function handle(EmailConfirmation $data): NewAccessToken
    {
        /** @var User */
        $user = User::query()->whereGmailLogin($data->email)->first();

        /** @var ?Otp */
        $otp = Otp::query()->where('email', $data->email)->where('otp', $data->otp)->first();

        $this->checkOtpValid($otp);

        $this->verified($user);

        return $this->generateToken($user);
    }

    /**
     * @throws InvalidOtpException
     */
    private function checkOtpValid(Otp|null $otp): void
    {
        if ( ! $otp) {
            throw new InvalidOtpException(
                message: strval(trans('auth.invalid_otp')),
                code: Status::UNPROCESSABLE_CONTENT->value,
            );
        }

        if ($otp->expired_at->lessThan(now())) {
            throw new InvalidOtpException(
                message: strval(trans('auth.otp_expired')),
                code: Status::UNPROCESSABLE_CONTENT->value,
            );
        }
    }

    private function verified(User $user): void
    {
        $user->update(['email_verified_at' => now()]);
    }

    private function generateToken(User $user): NewAccessToken
    {
        $user->revokeTokens();

        return $user->createToken($user->email);
    }
}
