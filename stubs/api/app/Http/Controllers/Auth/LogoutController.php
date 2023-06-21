<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Commands\Auth\Logout;
use Illuminate\Contracts\Support\Responsable;
use Thuraaung\ApiHelpers\Http\Responses\MessageResponse;

use function trans;

final class LogoutController
{
    public function __construct(
        private readonly Logout $command,
    ) {
    }

    public function __invoke(): Responsable
    {
        return new MessageResponse(
            message: $this->command->handle() ?
                trans('message.logout.success') :
                trans('message.logout.fail'),
        );
    }
}
