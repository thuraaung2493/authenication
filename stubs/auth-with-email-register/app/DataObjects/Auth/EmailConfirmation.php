<?php

declare(strict_types=1);

namespace App\DataObjects\Auth;

use Thuraaung\MakeFiles\Contracts\DataObjectContract;

final readonly class EmailConfirmation implements DataObjectContract
{
    public function __construct(
        public string $email,
        public string $otp,
    ) {
    }

    /**
     * @param array{email:string,otp:string} $attributes
     *
     * @return EmailConfirmation
     */
    public static function of(array $attributes): EmailConfirmation
    {
        return new EmailConfirmation(
            email: $attributes['email'],
            otp: $attributes['otp'],
        );
    }

    /**
     * @return array{email:string,otp:string}
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'otp' => $this->otp,
        ];
    }
}
