<?php

declare(strict_types=1);

use Illuminate\Support\Str;

dataset('validation_emails', [
    'email is not string' => 12,
    'email format is wrong' => 'test',
    'email is null' => null,
    'email length is more than 255' => Str::random(256),
    'email does not already exist' => 'doesnotexist@gmail.com',
]);

dataset('validation_otps', [
    'opt is not string' => 12,
    'otp is null' => null,
    'otp length is less than 6' => '12345',
    'otp does not already exist' => '123456',
]);
