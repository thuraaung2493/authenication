<?php

declare(strict_types=1);

use App\Http\Controllers\V1\Auth\EmailLoginController;
use App\Http\Controllers\V1\Auth\LogoutController;
use App\Http\Controllers\V1\Users\ShowController;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use JustSteveKing\StatusCode\Http;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeaders;

beforeEach(function (): void {
    withAppKeyHeaders();
});

test('If there are no app keys, it is not possible to logout', function (): void {
    withHeaders([
        'app-id' => null,
        'app-secrete' => null,
    ]);

    Sanctum::actingAs(User::factory()->create());

    deleteJson(
        uri: action(LogoutController::class),
    )
        ->assertStatus(Http::FORBIDDEN->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('message.exceptions.title.unauthorized'))
                ->where('description', \trans('message.exceptions.permission_denied'))
                ->where('status', Http::FORBIDDEN->value)
        );
});

test('If the app keys are outdated, it is not possible to logout', function (): void {
    withAppKeyHeaders(true);

    Sanctum::actingAs(User::factory()->create());

    deleteJson(
        uri: action(LogoutController::class),
    )
        ->assertStatus(Http::UPGRADE_REQUIRED->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('message.exceptions.title.outdated'))
                ->where('description', \trans('message.exceptions.invalid_app_keys'))
                ->where('status', Http::UPGRADE_REQUIRED->value)
        );
});

test('An unauthenticated user cannot log out', function (): void {
    deleteJson(
        uri: action(LogoutController::class),
    )
        ->assertStatus(Http::UNAUTHORIZED->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('message.exceptions.title.unauthenicated'))
                ->where('description', 'Unauthenticated.')
                ->where('status', Http::UNAUTHORIZED->value)
        );
});

it('returns the correct status code', function (): void {
    /** @var User */
    $user = User::factory()->create(['email' => 'test@gmail.com']);

    Sanctum::actingAs($user);

    deleteJson(
        uri: action(LogoutController::class),
    )
        ->assertStatus(Http::OK->value);

    expect($user->tokens()->count())->toEqual(0);
});

it('returns the correct payload', function (): void {
    /** @var User */
    $user = User::factory()->create(['email' => 'test@gmail.com']);

    Sanctum::actingAs($user);

    deleteJson(
        uri: action(LogoutController::class),
    )
        ->assertStatus(Http::OK->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('message', \trans('message.logout.success'))
        );

    expect($user->tokens()->count())->toEqual(0);
});

it('should not delete tokens that belong to the same account on other devices', function (): void {
    /** @var User */
    $user = User::factory()
        ->withPassword()
        ->withVerified()
        ->create(['email' => 'test@gmail.com']);

    $other = postJson(
        uri: action(EmailLoginController::class),
        data: ['email' => $user->email, 'password' => 'password'],
    )->assertOk();

    $response = postJson(
        uri: action(EmailLoginController::class),
        data: ['email' => $user->email, 'password' => 'password'],
    )->assertOk();

    deleteJson(
        uri: action(LogoutController::class),
        headers: ['Authorization' => 'Bearer ' . $response->json('token')]
    )
        ->assertStatus(Http::OK->value);

    expect($user->tokens()->count())->toEqual(1);

    getJson(
        uri: action(ShowController::class),
        headers: ['Authorization' => 'Bearer ' . $other->json('token')]
    )->assertOk();
});
