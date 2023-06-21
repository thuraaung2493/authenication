<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Auth;

use App\Actions\Auth\ForgotPassword;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Contracts\Support\Responsable;
use Thuraaung\ApiHelpers\Http\Responses\MessageResponse;

final readonly class ForgotPasswordController
{
    public function __construct(
        private ForgotPassword $command,
    ) {
    }

    public function __invoke(ForgotPasswordRequest $request): Responsable
    {
        $status = $this->command->handle(
            email: $request->string('email')->toString(),
        );

        return new MessageResponse(
            message: $status ?
                \trans('message.password.forgot') :
                \trans('message.password.forgot_fail'),
        );
    }
}
