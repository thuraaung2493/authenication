<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Language;
use App\Enums\LoginType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'password' => null,
            'login_type' => LoginType::GOOGLE->value,
            'login_id' => null,
            'device_token' => null,
            'profile' => null,
            'language' => Language::EN,
        ];
    }

    public function google(): UserFactory
    {
        return $this->state(
            state: fn (array $attributes) => [
                'login_type' => LoginType::GOOGLE->value,
                'login_id' => Str::uuid(),
            ],
        );
    }

    public function facebook(): UserFactory
    {
        return $this->state(
            state: fn (array $attributes) => [
                'login_type' => LoginType::FACEBOOK->value,
                'login_id' => Str::uuid(),
            ],
        );
    }

    public function en(): UserFactory
    {
        return $this->state(
            state: fn (array $attributes) => [
                'language' => Language::EN
            ],
        );
    }

    public function mm(): UserFactory
    {
        return $this->state(
            state: fn (array $attributes) => [
                'language' => Language::MM
            ],
        );
    }

    public function withDeviceToken(): UserFactory
    {
        return $this->state(
            state: fn (array $attributes) => [
                'device_token' => Str::random(),
            ],
        );
    }

    public function withProfile(): UserFactory
    {
        return $this->state(
            state: fn (array $attributes) => [
                'profile' => $this->faker->image(width: 5, height: 5)
            ],
        );
    }
}
