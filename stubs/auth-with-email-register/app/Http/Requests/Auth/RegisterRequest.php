<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\DataObjects\Auth\EmailRegisterInfo;
use App\Http\Requests\Concerns\FailedValidation;
use App\Http\Requests\Concerns\PayloadRequestContract;
use App\Rules\CheckEmailLoginUnique;
use Illuminate\Foundation\Http\FormRequest;

final class RegisterRequest extends FormRequest implements PayloadRequestContract
{
    use FailedValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                new CheckEmailLoginUnique(),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
        ];
    }

    public function payload(): EmailRegisterInfo
    {
        return EmailRegisterInfo::of(
            attributes: [
                "name" => $this->string('name')->toString(),
                "email" => $this->string('email')->toString(),
                "password" => $this->string('password')->toString(),
            ],
        );
    }
}
