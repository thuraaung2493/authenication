<?php

declare(strict_types=1);

use App\Http\Controllers\V1\Auth\ForgotPasswordController;
use App\Mail\SendOtpCode;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\Fluent\AssertableJson;
use JustSteveKing\StatusCode\Http;

use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeaders;

beforeEach(function (): void {
    withAppKeyHeaders();
});

test('If there are no app keys, it is not possible to forgot password', function (): void {
    withHeaders([
        'app-id' => null,
        'app-secrete' => null,
    ]);

    postJson(
        uri: action(ForgotPasswordController::class),
        data: [],
    )
        ->assertStatus(Http::FORBIDDEN->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('message.exceptions.title.unauthorized'))
                ->where('description', \trans('message.exceptions.permission_denied'))
                ->where('status', Http::FORBIDDEN->value)
        );
});

test('If the app keys are outdated, it is not possible to forgot password', function (): void {
    withAppKeyHeaders(true);

    postJson(
        uri: action(ForgotPasswordController::class),
        data: [],
    )
        ->assertStatus(Http::UPGRADE_REQUIRED->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('message.exceptions.title.outdated'))
                ->where('description', \trans('message.exceptions.invalid_app_keys'))
                ->where('status', Http::UPGRADE_REQUIRED->value)
        );
});

it('returns the validation errors when email dose not meet requirements', function ($email): void {
    postJson(
        uri: action(ForgotPasswordController::class),
        data: ['email' => $email],
    )
        ->assertStatus(Http::UNPROCESSABLE_ENTITY->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('email'));
})->with('validation_emails');

it('returns the validation errors when email dose not exists in database', function (): void {
    postJson(
        uri: action(ForgotPasswordController::class),
        data: ['email' => 'notexists@gmail.com'],
    )
        ->assertStatus(Http::UNPROCESSABLE_ENTITY->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('email'));
});

it('returns the correct status code and sends an OTP code via email', function (): void {
    Mail::fake();

    $user = User::factory()->create(['email' => 'test@gmail.com']);

    postJson(
        uri: action(ForgotPasswordController::class),
        data: ['email' => $user->email],
    )
        ->assertStatus(Http::OK->value);

    Mail::assertQueued(SendOtpCode::class);
});

it('returns the correct payload and sends an OTP code via email', function (): void {
    Mail::fake();

    $user = User::factory()->create(['email' => 'test@gmail.com']);

    postJson(
        uri: action(ForgotPasswordController::class),
        data: ['email' => $user->email],
    )
        ->assertStatus(Http::OK->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('message', \trans('message.password.forgot'))
                ->whereType('message', 'string')
        );

    $otp = Otp::query()->where('email', $user->email)->first();
    expect($otp)->not->toBeNull();
    expect($otp->email)->toBe($user->email);
    expect($otp->otp)->not->toBeNull();
    expect($otp->expired_at->greaterThanOrEqualTo(now()))->toBeTrue();

    Mail::assertQueued(SendOtpCode::class);
    Mail::assertQueued(
        fn (SendOtpCode $mail) =>
        $mail->otp->otp === $otp->otp &&
            $mail->user->name === $user->name &&
            $mail->user->email === $user->email
    );
});
