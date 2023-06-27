<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\DataObjects\Auth\EmailRegisterInfo;
use App\Exceptions\Auth\EmailRegisterException;
use App\Models\User;
use App\Queries\Users\FetchGmailUser;
use Illuminate\Support\Facades\DB;

use function trans;

final readonly class EmailRegister
{
    public function __construct(
        private CreateOtp $createOtp,
        private SendOtp $sendOtp,
        private FetchGmailUser $fetchGmailUser,
    ) {
    }

    public function handle(EmailRegisterInfo $data): void
    {
        $this->checkAlreadyRegister($data->email);

        DB::transaction(function () use ($data): void {

            $user = User::query()->create(
                attributes: $data->toArray(),
            );

            $otp = $this->createOtp->handle(
                email: $data->email,
            );

            $this->sendOtp->handle(
                user: $user,
                otp: $otp
            );
        });
    }

    /**
     * @throws EmailRegisterException
     */
    private function checkAlreadyRegister(string $email): void
    {
        if (null !== $this->fetchGmailUser->handle($email)) {
            throw new EmailRegisterException(
                message: trans('auth.exceptions.email_not_verified'),
                code: Http::NOT_ACCEPTABLE->value,
            );
        }
    }
}
