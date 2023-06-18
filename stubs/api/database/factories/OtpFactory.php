<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Otp;
use App\Services\Generators\Otp\OtpGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Otp>
 */
final class OtpFactory extends Factory
{
    protected $model = Otp::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'otp' => (new OtpGenerator())->generate(),
            'expired_at' => now()->addMinute(),
        ];
    }
}
