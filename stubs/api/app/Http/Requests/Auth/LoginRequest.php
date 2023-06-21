<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DataObjects\Auth\EmailLoginCredentials;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\FailedValidation;
use App\DataObjects\Auth\SocialLoginInfo;
use App\Enums\LoginType;
use App\Http\Requests\Concerns\PayloadRequestContract;
use Illuminate\Validation\Rule;

final class LoginRequest extends FormRequest implements PayloadRequestContract
{
    use FailedValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if (LoginType::GMAIL->match($this->type)) {
            return $this->emailLoginRules();
        }

        return $this->socialLoginRules();
    }

    private function emailLoginRules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::exists(User::class, 'email'),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
        ];
    }

    private function socialLoginRules(): array
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

    public function payload(): SocialLoginInfo|EmailLoginCredentials
    {
        if (LoginType::GMAIL->match($this->type)) {
            return $this->getEmailLoginCredentials();
        }

        return $this->getSocialLoginInfo();
    }

    private function getEmailLoginCredentials(): EmailLoginCredentials
    {
        return EmailLoginCredentials::of(
            attributes: [
                'email' => $this->string('email')->toString(),
                'password' => $this->string('password')->toString(),
            ],
        );
    }

    private function getSocialLoginInfo(): SocialLoginInfo
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
