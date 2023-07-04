<?php

declare(strict_types=1);

use App\Enums\LoginType;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Thuraaung\ApiHelpers\Http\Enums\Status;

use function Pest\Laravel\deleteJson;
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
        ->assertStatus(Status::FORBIDDEN->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('auth.exceptions.title.unauthorized'))
                ->where('description', \trans('auth.permission_denied'))
                ->where('status', Status::FORBIDDEN->value)
        );
});

test('If the app keys are outdated, it is not possible to logout', function (): void {
    withAppKeyHeaders(true);

    Sanctum::actingAs(User::factory()->create());

    deleteJson(
        uri: action(LogoutController::class),
    )
        ->assertStatus(Status::UPGRADE_REQUIRED->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('auth.exceptions.title.outdated'))
                ->where('description', \trans('auth.invalid_app_keys'))
                ->where('status', Status::UPGRADE_REQUIRED->value)
        );
});

test('An unauthenticated user cannot log out', function (): void {
    deleteJson(
        uri: action(LogoutController::class),
    )
        ->assertStatus(Status::UNAUTHORIZED->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('auth.exceptions.title.unauthenticated'))
                ->where('description', 'Unauthenticated.')
                ->where('status', Status::UNAUTHORIZED->value)
        );
});

it('returns the correct status code', function (): void {
    /** @var User */
    $user = User::factory()->create(['email' => 'test@gmail.com']);

    Sanctum::actingAs($user);

    deleteJson(
        uri: action(LogoutController::class),
    )
        ->assertStatus(Status::OK->value);

    expect($user->tokens()->count())->toEqual(0);
});

it('returns the correct payload', function (): void {
    /** @var User */
    $user = User::factory()->create(['email' => 'test@gmail.com']);

    Sanctum::actingAs($user);

    deleteJson(
        uri: action(LogoutController::class),
    )
        ->assertStatus(Status::OK->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('message', \trans('auth.logout.success'))
        );

    expect($user->tokens()->count())->toEqual(0);
});

it('should not delete tokens that belong to the same account on other devices', function (): void {
    /** @var User */
    $user = User::factory()
        ->google()
        ->make();

    $other = postJson(
        uri: action(LoginController::class, ['type' => LoginType::GOOGLE->value]),
        data: $user->toArray(),
    )->assertOk();

    $response = postJson(
        uri: action(LoginController::class, ['type' => LoginType::GOOGLE->value]),
        data: $user->toArray(),
    )->assertOk();

    expect($user->tokens()->count())->toEqual(2);

    deleteJson(
        uri: action(LogoutController::class),
        headers: ['Authorization' => 'Bearer ' . $response->json('token')]
    )
        ->assertStatus(Status::OK->value);

    expect($user->tokens()->count())->toEqual(1);
});
