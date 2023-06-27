<?php

declare(strict_types=1);

namespace App\Exceptions\Concerns;

use Illuminate\Contracts\Support\Responsable;
use Thuraaung\ApiHelpers\Http\Enums\Status;
use Thuraaung\ApiHelpers\Http\Responses\ApiErrorResponse;

/**
 * @property-read string $message
 * @property-read int $code
 */
trait HasRender
{
    public const TITLE = 'Exception!';

    public function render(): Responsable|bool
    {
        if (request()->isJson()) {
            return new ApiErrorResponse(
                title: self::TITLE,
                description: $this->message,
                status: Status::from($this->code),
            );
        }

        return false;
    }
}
