<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\FailedValidation;
use App\DataObjects\EmailLoginCredentials;
use App\Http\Requests\Concerns\PayloadRequestContract;

final class EmailLoginRequest extends FormRequest implements PayloadRequestContract
{
    use FailedValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::exists('users', 'email'),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
        ];
    }

    public function payload(): EmailLoginCredentials
    {
        return EmailLoginCredentials::of(
            attributes: [
                'email' => $this->string('email')->toString(),
                'password' => $this->string('password')->toString(),
            ],
        );
    }
}
