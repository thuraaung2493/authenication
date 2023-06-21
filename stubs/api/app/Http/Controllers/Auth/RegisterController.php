<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Commands\Auth\EmailRegister;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Contracts\Support\Responsable;
use Thuraaung\ApiHelpers\Http\Responses\MessageResponse;

use function trans;

final class RegisterController
{
    public function __construct(
        private readonly EmailRegister $command
    ) {
    }

    public function __invoke(RegisterRequest $request): Responsable
    {
        $this->command->handle(
            data: $request->payload(),
        );

        return new MessageResponse(
            message: trans('message.register.success'),
        );
    }
}
