<?php

declare(strict_types=1);

namespace Modules\Tenants\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Tenants\Domain\Models\Location;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Tenants\Domain\Models\Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'country_code' => fake()->countryCode(),
            'country_name' => fake()->country(),
            'region_name' => fake()->state(),
            'city' => fake()->city(),
            'address_line' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'timezone' => fake()->timezone(),
        ];
    }

    /**
     * Create location with specific country code
     */
    public function forCountry(string $countryCode): static
    {
        return $this->state(fn (array $attributes) => [
            'country_code' => $countryCode,
        ]);
    }

    /**
     * Create location in specific city
     */
    public function inCity(string $city): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => $city,
        ]);
    }

    /**
     * Create location without optional fields
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'region_name' => null,
            'address_line' => null,
            'postal_code' => null,
            'timezone' => null,
        ]);
    }
}
