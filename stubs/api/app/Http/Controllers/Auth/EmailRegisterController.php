<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Commands\Auth\EmailRegister;
use App\Http\Requests\Auth\EmailRegisterRequest;
use Illuminate\Contracts\Support\Responsable;
use Thuraaung\ApiHelpers\Http\Responses\MessageResponse;

use function trans;

final class EmailRegisterController
{
    public function __construct(
        private readonly EmailRegister $emailRegister
    ) {
    }

    public function __invoke(EmailRegisterRequest $request): Responsable
    {
        $this->emailRegister->handle(
            data: $request->payload(),
        );

        return new MessageResponse(
            message: trans('message.register.success'),
        );
    }
}
