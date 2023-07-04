<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\OtpResendController;
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

test('If there are no app keys, it is not possible to resend otp', function (): void {
    withHeaders([
        'app-id' => null,
        'app-secrete' => null,
    ]);

    postJson(
        uri: action(OtpResendController::class),
    )
        ->assertStatus(Http::FORBIDDEN->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('auth.exceptions.title.unauthorized'))
                ->where('description', \trans('auth.permission_denied'))
                ->where('status', Http::FORBIDDEN->value)
        );
});

test('If the app keys are outdated, it is not possible to resend otp', function (): void {
    withAppKeyHeaders(true);

    postJson(
        uri: action(OtpResendController::class),
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
    User::factory()->create(['email' => 'test@gmail.com']);

    postJson(
        uri: action(OtpResendController::class),
        data: ['email' => $email],
    )
        ->assertStatus(Http::UNPROCESSABLE_ENTITY->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('email'));
})->with('validation_emails');

it('returns the correct status code and sends an OTP code via email', function (): void {
    Mail::fake();

    /** @var User */
    $user = User::factory()->create(['email' => 'test@gmail.com']);

    postJson(
        uri: action(OtpResendController::class),
        data: ['email' => $user->email],
    )
        ->assertStatus(Http::OK->value);

    Mail::assertQueued(SendOtpCode::class);
});

it('returns the correct payload and sends an OTP code via email', function (): void {
    Mail::fake();

    /** @var User */
    $user = User::factory()->create(['email' => 'test@gmail.com']);

    /** @var Otp */
    $otp = Otp::factory()->create(['email' => $user->email, 'expired_at' => now()->subMinute()]);

    postJson(
        uri: action(OtpResendController::class),
        data: ['email' => $user->email],
    )
        ->assertStatus(Http::OK->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('message', \trans('auth.otp_resend'))
                ->whereType('message', 'string')
        );

    assertDatabaseCount('otps', 1);
    $otp->refresh();
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
