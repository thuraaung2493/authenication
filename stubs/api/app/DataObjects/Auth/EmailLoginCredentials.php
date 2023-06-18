<?php

declare(strict_types=1);

namespace App\DataObjects;

use Thuraaung\MakeFiles\Contracts\DataObjectContract;

final readonly class EmailLoginCredentials implements DataObjectContract
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }

    /**
     * @param array{email:string,password:string} $attributes
     *
     * @return EmailLoginCredentials
     */
    public static function of(array $attributes): EmailLoginCredentials
    {
        return new EmailLoginCredentials(
            email: $attributes['email'],
            password: $attributes['password'],
        );
    }

    /**
     * @return array{email:string,password:string}
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
