<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Thuraaung\ApiHelpers\Http\Responses\ApiValidationErrorsResponse;

use function trans;

trait FailedValidation
{
    protected function failedValidation(Validator $validator): void
    {
        $response = new ApiValidationErrorsResponse(
            title: trans('auth.exceptions.title.validation'),
            errors: $validator->errors(),
        );

        throw new HttpResponseException(
            response: $response->toResponse($this),
        );
    }
}
