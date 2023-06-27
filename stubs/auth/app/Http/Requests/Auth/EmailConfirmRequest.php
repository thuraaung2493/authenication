<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Otps;

use App\DataObjects\Auth\EmailConfirmation;
use App\Http\Requests\Concerns\FailedValidation;
use App\Http\Requests\Concerns\PayloadRequestContract;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Thuraaung\MakeFiles\Contracts\DataObjectContract;

use function config;

final class EmailVerifyRequest extends FormRequest implements PayloadRequestContract
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
                Rule::exists(User::class, 'email'),
            ],
            'otp' => [
                'required',
                'string',
                'size:6',
                Rule::when(
                    condition: 'production' === config('app.env'),
                    rules: Rule::exists(Otp::class, 'otp')
                ),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => 'User does not register with this email.',
        ];
    }

    public function payload(): DataObjectContract
    {
        return EmailConfirmation::of(
            attributes: [
                'email' => $this->string('email')->toString(),
                'otp' => $this->string('otp')->toString(),
            ],
        );
    }
}
