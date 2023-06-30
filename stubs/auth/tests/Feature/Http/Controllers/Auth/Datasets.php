<?php

declare(strict_types=1);

use App\Enums\LoginType;
use Illuminate\Support\Str;

dataset('login_types', [
    'facebook' => LoginType::FACEBOOK->value,
    'google' => LoginType::GOOGLE->value,
]);

dataset('validation_names', [
    'name is not string' => 12,
    'name is null' => null,
    'name length is more than 255' => Str::random(256),
]);

dataset('validation_emails', [
    'email is not string' => 12,
    'email format is wrong' => 'test',
    'email is null' => null,
    'email length is more than 255' => Str::random(256),
]);

dataset('validation_passwords', [
    'password is not string' => 12,
    'password is null' => null,
    'password length is less than 8' => Str::random(7),
    'password length is more than 255' => Str::random(256),
]);

dataset('validation_phones', [
    'phone is not string' => 12,
    'phone is null' => null,
    'phone length is more than 255' => Str::random(256),
]);

dataset('validation_login_ids', [
    'login_id is null' => null,
    'login_id is not string' => 12,
    'login_id length is more than 255' => Str::random(256),
]);

dataset('validation_profiles', [
    'profile is not string' => 12,
    'profile is invalid url' => 'image',
    'profile is inactive url' => 'https:://inactive.com/images',
]);
