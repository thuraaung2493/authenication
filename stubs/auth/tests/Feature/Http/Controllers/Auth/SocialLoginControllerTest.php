<?php

declare(strict_types=1);

use App\Enums\Language;
use App\Enums\LoginType;
use App\Http\Controllers\Auth\LoginController;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Thuraaung\ApiHelpers\Http\Enums\Status;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeaders;

function getSocialLoginData(string $type, array $attributes = []): array
{
    return LoginType::GOOGLE->match($type) ?
        User::factory()->google()->make($attributes)->toArray() :
        User::factory()->facebook()->make($attributes)->toArray();
}

beforeEach(function (): void {
    withAppKeyHeaders();
});

test('If there are no app keys, it is not possible to social login', function (): void {
    withHeaders([
        'app-id' => null,
        'app-secrete' => null,
    ]);

    postJson(
        uri: action(LoginController::class, ['type' => 'google']),
    )
        ->assertStatus(Status::FORBIDDEN->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('auth.exceptions.title.unauthorized'))
                ->where('description', \trans('auth.permission_denied'))
                ->where('status', Status::FORBIDDEN->value)
        );
});

test('If the app keys are outdated, it is not possible to social login', function (): void {
    withAppKeyHeaders(true);

    postJson(
        uri: action(LoginController::class, ['type' => 'google']),
    )
        ->assertStatus(Status::UPGRADE_REQUIRED->value)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('title', \trans('auth.exceptions.title.outdated'))
                ->where('description', \trans('auth.invalid_app_keys'))
                ->where('status', Status::UPGRADE_REQUIRED->value)
        );
});

it('returns the validation errors when name dose not meet requirements', function ($name, $type): void {
    postJson(
        uri: action(LoginController::class, ['type' => $type]),
        data: getSocialLoginData($type, ['name' => $name]),
    )
        ->assertStatus(Status::UNPROCESSABLE_CONTENT->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('name'));
})->with('validation_names')->with('login_types');

it('returns the validation errors when email dose not meet requirements', function ($email, $type): void {
    postJson(
        uri: action(LoginController::class, ['type' => $type]),
        data: getSocialLoginData($type, ['email' => $email, 'phone' => null]),
    )
        ->assertStatus(Status::UNPROCESSABLE_CONTENT->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('email'));
})->with('validation_emails')->with('login_types');

it('returns the correct status when the requests do not have email but phone', function ($type): void {
    postJson(
        uri: action(LoginController::class, ['type' => $type]),
        data: getSocialLoginData($type, ['email' => null]),
    )
        ->assertStatus(Status::OK->value);
})->with('login_types');

it('returns the validation errors when phone dose not meet requirements', function ($phone, $type): void {
    postJson(
        uri: action(LoginController::class, ['type' => $type]),
        data: getSocialLoginData($type, ['phone' => $phone, 'email' => null]),
    )
        ->assertStatus(Status::UNPROCESSABLE_CONTENT->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('phone'));
})->with('validation_phones')->with('login_types');

it('returns the correct status when the requests do not have phone but email', function ($type): void {
    postJson(
        uri: action(LoginController::class, ['type' => $type]),
        data: getSocialLoginData($type, ['phone' => null]),
    )
        ->assertStatus(Status::OK->value);
})->with('login_types');

it('returns the validation errors when profile dose not meet requirements', function ($profile, $type): void {
    mockProfileUploadNotCall();

    postJson(
        uri: action(LoginController::class, ['type' => $type]),
        data: getSocialLoginData($type, ['profile' => $profile]),
    )
        ->assertStatus(Status::UNPROCESSABLE_CONTENT->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('profile'));
})->with('validation_profiles')->with('login_types');

it('returns the validation errors when login id dose not meet requirements', function ($loginId, $type): void {
    postJson(
        uri: action(LoginController::class, ['type' => $type]),
        data: getSocialLoginData($type, ['login_id' => $loginId]),
    )
        ->assertStatus(Status::UNPROCESSABLE_CONTENT->value)
        ->assertJson(errorAssertJson())
        ->assertJsonStructure(validationJsonStructure('login_id'));
})->with('validation_login_ids')->with('login_types');

it('returns the correct status code when social login with profile', function ($type): void {
    mockProfileUpload('test.png');

    postJson(
        uri: action(LoginController::class, ['type' => $type]),
        data: getSocialLoginData($type, [
            'profile' => fake()->imageUrl(width: 5, height: 5),
        ]),
    )
        ->assertStatus(Status::OK->value);

    assertDatabaseCount('users', 1);
    assertDatabaseHas('users', ['profile' => 'profiles/test.png']);
})->with('login_types');

it('returns the correct status code when social login without profile', function ($type): void {
    mockProfileUploadNotCall();

    postJson(
        uri: action(LoginController::class, ['type' => $type]),
        data: getSocialLoginData($type),
    )
        ->assertStatus(Status::OK->value);

    assertDatabaseCount('users', 1);
    assertDatabaseHas('users', ['profile' => null]);
})->with('login_types');

it('returns the correct payload when social login with profile', function ($type): void {
    mockProfileUpload('test.png');

    postJson(
        uri: action(LoginController::class, ['type' => $type]),
        data: getSocialLoginData($type, [
            'profile' => fake()->imageUrl(width: 5, height: 5)
        ]),
    )
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->has('token')
                ->whereType('token', 'string')
                ->where('message', 'Success.')
                ->where('status', Status::OK->value)
        );

    assertDatabaseCount('users', 1);
    assertDatabaseHas('users', ['login_type' => $type]);
    assertDatabaseHas('users', ['language' => Language::EN->value]);
    assertDatabaseHas('users', ['profile' => 'profiles/test.png']);
})->with('login_types');

it('returns the correct payload when social login without profile', function ($type): void {
    mockProfileUploadNotCall();

    postJson(
        uri: action(LoginController::class, ['type' => $type]),
        data: getSocialLoginData($type),
    )
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->has('token')
                ->whereType('token', 'string')
                ->where('message', 'Success.')
                ->where('status', Status::OK->value)
        );

    assertDatabaseCount('users', 1);
    assertDatabaseHas('users', ['login_type' => $type]);
    assertDatabaseHas('users', ['language' => Language::EN->value]);
    assertDatabaseHas('users', ['profile' => null]);
})->with('login_types');

test('When a user logs in with the same social account, it should update the existing account', function ($type): void {
    $user = LoginType::FACEBOOK->match($type) ?
        User::factory()->facebook()->create() :
        User::factory()->google()->create();

    postJson(
        uri: action(LoginController::class, ['type' => $type]),
        data: [
            ...$user->toArray(),
            'name' => 'New Name',
            'email' => 'newname@gmail.com',
            'phone' => '09822828282828',
        ],
    )
        ->assertStatus(Status::OK->value);

    assertDatabaseCount('users', 1);
    assertDatabaseHas('users', [
        'name' => 'New Name',
        'email' => 'newname@gmail.com',
        'phone' => '09822828282828',
    ]);
})->with('login_types');
