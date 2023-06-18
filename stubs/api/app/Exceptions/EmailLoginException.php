<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Thuraaung\ApiHelpers\Http\Enums\Status;
use Thuraaung\ApiHelpers\Http\Responses\ApiErrorResponse;

final class EmailLoginException extends Exception
{
    public function render(): Responsable
    {
        return new ApiErrorResponse(
            title: 'Email Login Failed!',
            description: $this->message,
            status: Status::from($this->code),
        );
    }
}
