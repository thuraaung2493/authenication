<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\RegisterController;
use App\Mail\SendOtpCode;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\Fluent\AssertableJson;
use JustSteveKing\StatusCode\Http;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeaders;

beforeEach(function (): void {
    withAppKeyHeaders();
});

test('If there are no app keys, it is not possible to register with an email', function (): void {
    withHeaders([
        'app-id' => null,
        'app-secrete' => null,
    ]);

    postJson(
        uri: action(RegisterController::class),
        data: [],
    )->assertStatus(Http::FORBIDDEN->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('message.exceptions.title.unauthorized'))
                ->where('description', \trans('message.exceptions.permission_denied'))
                ->where('status', Http::FORBIDDEN->value)
        );
});

test('If the app keys are outdated, it is not possible to register with an email', function (): void {
    withAppKeyHeaders(true);

    postJson(
        uri: action(RegisterController::class),
        data: [],
    )->assertStatus(Http::UPGRADE_REQUIRED->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('message.exceptions.title.outdated'))
                ->where('description', \trans('message.exceptions.invalid_app_keys'))
                ->where('status', Http::UPGRADE_REQUIRED->value)
        );
});

it('returns the validation errors when name dose not meet requirements', function ($name): void {
    postJson(
        uri: action(RegisterController::class),
        data: [
            'name' => $name,
            'email' => 'test@gmail.com',
            'password' => 'password',
        ],
    )
        ->assertStatus(Http::UNPROCESSABLE_ENTITY->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('name'));
})->with('validation_names');

it('returns the validation errors when email dose not meet requirements', function ($email): void {
    postJson(
        uri: action(RegisterController::class),
        data: [
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password',
        ]
    )
        ->assertStatus(Http::UNPROCESSABLE_ENTITY->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('email'));
})->with('validation_emails');

it('returns the validation errors when email is duplicate', function (): void {
    $email = 'test@gmail.com';
    User::factory()->withPassword()->create(['email' => $email]);

    postJson(
        uri: action(RegisterController::class),
        data: [
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password',
        ],
    )
        ->assertStatus(Http::NOT_ACCEPTABLE->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('message.exceptions.title.email_not_verified'))
                ->where('description', \trans('message.exceptions.email_not_verified'))
                ->where('status', Http::NOT_ACCEPTABLE->value)
        );
});

it('returns the validation errors when password dose not meet requirements', function ($password): void {
    postJson(
        uri: action(RegisterController::class),
        data: [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => $password,
        ]
    )
        ->assertStatus(Http::UNPROCESSABLE_ENTITY->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('password'));
})->with('validation_passwords');

it('returns the correct status code and sends an OTP code via email', function (): void {
    Mail::fake();

    postJson(
        uri: action(RegisterController::class),
        data: [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'password',
        ],
    )
        ->assertStatus(Http::OK->value);

    Mail::assertQueued(SendOtpCode::class);
});

it('returns the correct payload, the correct data exists in database and sends an OTP code via email', function (): void {
    Mail::fake();

    $data = [
        'name' => 'Test User',
        'email' => 'test@gmail.com',
        'password' => 'password',
    ];

    postJson(
        uri: action(RegisterController::class),
        data: $data,
    )
        ->assertStatus(Http::OK->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('message', \trans('message.register.success'))
                ->whereType('message', 'string')
        );

    assertDatabaseCount('users', 1);

    $user = User::query()->whereGmailLogin($data['email'])->first();
    expect($user->name)->toBe($data['name']);
    expect($user->email)->toBe($data['email']);
    expect($user->password)->not->toBeNull();

    $otp = Otp::query()->where('email', $data['email'])->first();
    expect($otp)->not->toBeNull();
    expect($otp->email)->toBe($data['email']);
    expect($otp->otp)->not->toBeNull();

    Mail::assertQueued(SendOtpCode::class);
    Mail::assertQueued(
        fn (SendOtpCode $mail) =>
        $mail->otp->otp === $otp->otp &&
            $mail->user->name === $user->name &&
            $mail->user->email === $user->email
    );
});
