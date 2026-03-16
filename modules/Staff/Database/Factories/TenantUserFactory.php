<?php

declare(strict_types=1);

namespace Modules\Staff\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Staff\Domain\Models\TenantUser;

/**
 * @extends Factory<TenantUser>
 */
class TenantUserFactory extends Factory
{
    protected $model = TenantUser::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->e164PhoneNumber(),
            'password' => 'password',
            'preferred_language' => 'en',
            'is_active' => true,
            'activated_at' => now(),
            'setup_token' => null,
            'setup_token_expires_at' => null,
            'last_login_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function inactive(): static
    {
        $plainToken = Str::random(32);

        return $this->state(fn (array $attributes) => [
            'password' => null,
            'is_active' => false,
            'activated_at' => null,
            'setup_token' => hash('sha256', $plainToken),
            'setup_token_expires_at' => now()->addHours(48),
        ])->afterCreating(function (TenantUser $user) use ($plainToken) {
            $user->plain_setup_token = $plainToken;
        });
    }

    public function expiredToken(): static
    {
        $plainToken = Str::random(32);

        return $this->state(fn (array $attributes) => [
            'password' => null,
            'is_active' => false,
            'activated_at' => null,
            'setup_token' => hash('sha256', $plainToken),
            'setup_token_expires_at' => now()->subHour(),
        ])->afterCreating(function (TenantUser $user) use ($plainToken) {
            $user->plain_setup_token = $plainToken;
        });
    }

    public function deactivated(): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => 'password',
            'is_active' => false,
            'activated_at' => now()->subDays(30),
            'setup_token' => null,
            'setup_token_expires_at' => null,
        ]);
    }
}
