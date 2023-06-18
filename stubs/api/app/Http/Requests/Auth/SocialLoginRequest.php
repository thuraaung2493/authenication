<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\FailedValidation;
use App\DataObjects\SocialLoginInfo;
use App\Http\Requests\Concerns\PayloadRequestContract;

final class SocialLoginRequest extends FormRequest implements PayloadRequestContract
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
                'required_without:phone',
                'nullable',
                'string',
                'email',
                'max:255',
            ],
            'phone' => [
                'required_without:email',
                'nullable',
                'string',
                'max:255',
            ],
            'login_id' => [
                'required',
                'string',
                'max:255',
            ],
            'profile' => [
                'nullable',
                'url',
                'active_url',
            ],
        ];
    }

    public function payload(): SocialLoginInfo
    {
        return SocialLoginInfo::of(
            attributes: [
                "name" => $this->string('name')->toString(),
                "email" => $this->string('email')->isNotEmpty() ? $this->string('email')->toString() : null,
                "phone" => $this->string('phone')->isNotEmpty() ? $this->string('phone')->toString() : null,
                'login_type' => $this->type,
                "login_id" => $this->string('login_id')->toString(),
                "profile" => $this->string('profile')->isNotEmpty() ? $this->string('profile')->toString() : null,
            ],
        );
    }
}
