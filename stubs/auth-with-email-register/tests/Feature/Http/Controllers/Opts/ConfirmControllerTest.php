<?php

declare(strict_types=1);

use App\Http\Controllers\V1\Opts\ConfirmController;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use JustSteveKing\StatusCode\Http;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeaders;

beforeEach(function (): void {
    withAppKeyHeaders();
    $email = 'test@gmail.com';
    User::factory()->create(['email' => $email]);
    Otp::factory()->create(['email' => $email, 'otp' => '000000']);
});

test('If there are no app keys, it is not possible to confirm otp', function (): void {
    withHeaders([
        'app-id' => null,
        'app-secrete' => null,
    ]);

    postJson(
        uri: action(ConfirmController::class),
    )
        ->assertStatus(Http::FORBIDDEN->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('auth.exceptions.title.unauthorized'))
                ->where('description', \trans('auth.permission_denied'))
                ->where('status', Http::FORBIDDEN->value)
        );
});

test('If the app keys are outdated, it is not possible to confirm otp', function (): void {
    withAppKeyHeaders(true);

    postJson(
        uri: action(ConfirmController::class),
    )
        ->assertStatus(Http::UPGRADE_REQUIRED->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('auth.exceptions.title.outdated'))
                ->where('description', \trans('auth.invalid_app_keys'))
                ->where('status', Http::UPGRADE_REQUIRED->value)
        );
});

it('returns the validation errors when email dose not meet requirements', function ($email): void {
    postJson(
        uri: action(ConfirmController::class),
        data: [
            'email' => $email,
            'otp' => '000000',
        ],
    )
        ->assertStatus(Http::UNPROCESSABLE_ENTITY->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('email'));
})->with('validation_emails');

it('should not confirm otp when otp code did not match in production env', function (): void {
    Config::set('app.env', 'production');

    User::factory()->create(['email' => 'newtest@gmail.com']);
    Otp::factory()->create(['email' => 'newtest@gmail.com', 'otp' => '123456']);

    postJson(
        uri: action(ConfirmController::class),
        data: [
            'email' => 'newtest@gmail.com',
            'otp' => '000000',
        ],
    )
        ->assertStatus(Http::UNPROCESSABLE_ENTITY->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('auth.exceptions.title.invalid_otp'))
                ->where('description', \trans('auth.invalid_otp'))
                ->where('status', Http::UNPROCESSABLE_ENTITY->value)
        );
});

it('should confirm otp when otp code did not match in local and dev env', function (): void {
    Config::set('app.env', 'local');

    User::factory()->create(['email' => 'newtest@gmail.com']);
    Otp::factory()->create(['email' => 'newtest@gmail.com', 'otp' => '123456']);

    postJson(
        uri: action(ConfirmController::class),
        data: [
            'email' => 'newtest@gmail.com',
            'otp' => '000000',
        ],
    )
        ->assertStatus(Http::OK->value);
});

it('returns the validation errors when otp dose not meet requirements', function ($otp): void {
    Config::set('app.env', 'production');

    postJson(
        uri: action(ConfirmController::class),
        data: [
            'email' => 'test@gmail.com',
            'otp' => $otp,
        ],
    )
        ->assertStatus(Http::UNPROCESSABLE_ENTITY->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('otp'));
})->with('validation_otps');

it('returns the correct status codes', function (): void {
    postJson(
        uri: action(ConfirmController::class),
        data: [
            'email' => 'test@gmail.com',
            'otp' => '000000',
        ],
    )
        ->assertStatus(Http::OK->value);
});

it('returns the correct payload', function (): void {
    postJson(
        uri: action(ConfirmController::class),
        data: [
            'email' => 'test@gmail.com',
            'otp' => '000000',
        ],
    )
        ->assertStatus(Http::OK->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->has('token')
                ->whereType('token', 'string')
        );
});
