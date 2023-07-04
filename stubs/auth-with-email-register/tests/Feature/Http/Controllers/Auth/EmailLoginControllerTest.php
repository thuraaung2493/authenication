<?php

declare(strict_types=1);

use App\Enums\LoginType;
use App\Http\Controllers\Auth\LoginController;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Thuraaung\ApiHelpers\Http\Enums\Status;

use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeaders;

beforeEach(function (): void {
    withAppKeyHeaders();
});

test('If there are no app keys, it is not possible to log in with an email', function (): void {
    withHeaders([
        'app-id' => null,
        'app-secrete' => null,
    ]);

    postJson(
        uri: action(LoginController::class, ['type' => LoginType::GMAIL->value]),
        data: [],
    )->assertStatus(Status::FORBIDDEN->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('auth.exceptions.title.unauthorized'))
                ->where('description', \trans('auth.permission_denied'))
                ->where('status', Status::FORBIDDEN->value)
        );
});

test('If the app keys are outdated, it is not possible to log in with an email', function (): void {
    withAppKeyHeaders(true);

    postJson(
        uri: action(LoginController::class, ['type' => LoginType::GMAIL->value]),
        data: [],
    )->assertStatus(Status::UPGRADE_REQUIRED->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('auth.exceptions.title.outdated'))
                ->where('description', \trans('auth.invalid_app_keys'))
                ->where('status', Status::UPGRADE_REQUIRED->value)
        );
});

it('returns the validation errors when email dose not meet requirements', function ($email): void {
    postJson(
        uri: action(LoginController::class, ['type' => LoginType::GMAIL->value]),
        data: [
            'email' => $email,
            'password' => 'password',
        ],
    )
        ->assertStatus(Status::UNPROCESSABLE_CONTENT->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('email'));
})->with('validation_emails');

it('returns the validation errors when password dose not meet requirements', function ($password): void {
    postJson(
        uri: action(LoginController::class, ['type' => LoginType::GMAIL->value]),
        data: [
            'email' => 'test@gmail.com',
            'password' => $password,
        ],
    )
        ->assertStatus(Status::UNPROCESSABLE_CONTENT->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('password'));
})->with('validation_passwords');

it('returns the validation errors when the email is not registered', function (): void {
    postJson(
        uri: action(LoginController::class, ['type' => LoginType::GMAIL->value]),
        data: [
            'email' => 'test@gmail.com',
            'password' => 'password',
        ],
    )
        ->assertStatus(Status::UNPROCESSABLE_CONTENT->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('email'));
});

test('The login attempt must fail when the password is incorrect', function (): void {
    User::factory()->withPassword('12345678')->create(['email' => 'test@gmail.com']);

    postJson(
        uri: action(LoginController::class, ['type' => LoginType::GMAIL->value]),
        data: [
            'email' => 'test@gmail.com',
            'password' => 'password',
        ],
    )
        ->assertStatus(Status::UNAUTHORIZED->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', 'Email Login Failed!')
                ->where('description', trans('auth.login_failed'))
                ->where('status', Status::UNAUTHORIZED->value)
        );
});

test('The login attempt must fail when the email is not verified', function (): void {
    User::factory()->withPassword()->create(['email' => 'test@gmail.com']);

    postJson(
        uri: action(LoginController::class, ['type' => LoginType::GMAIL->value]),
        data: [
            'email' => 'test@gmail.com',
            'password' => 'password',
        ],
    )
        ->assertStatus(Status::UNAUTHORIZED->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', 'Email Login Failed!')
                ->where('description', trans('auth.email_not_verified'))
                ->where('status', Status::UNAUTHORIZED->value)
        );
});

it('returns the correct status code', function (): void {
    User::factory()->withPassword()->withVerified()->create(['email' => 'test@gmail.com']);

    postJson(
        uri: action(LoginController::class, ['type' => LoginType::GMAIL->value]),
        data: [
            'email' => 'test@gmail.com',
            'password' => 'password',
        ],
    )
        ->assertStatus(Status::OK->value);
});

it('returns the correct payload', function (): void {
    User::factory()->withPassword()->withVerified()->create(['email' => 'test@gmail.com']);

    postJson(
        uri: action(LoginController::class, ['type' => LoginType::GMAIL->value]),
        data: [
            'email' => 'test@gmail.com',
            'password' => 'password',
        ],
    )
        ->assertStatus(Status::OK->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->has('token')
                ->whereType('token', 'string')
                ->where('message', 'Success.')
                ->where('status', Status::OK->value)
        );
});
