<?php

declare(strict_types=1);

namespace Modules\Tenants\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Tenants\Domain\Models\Tenant;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Tenants\Domain\Models\Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = fake()->words(2, true).' Hotel';
        $slug = Str::slug($name);

        return [
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'slug' => $slug,
            'database_name' => null,
            'owner_id' => 1,
            'subscription_id' => null,
            'location_id' => 1,
            'status' => 'active',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disabled',
        ]);
    }

    public function forOwner(int $ownerId): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => $ownerId,
        ]);
    }

    public function atLocation(int $locationId): static
    {
        return $this->state(fn (array $attributes) => [
            'location_id' => $locationId,
        ]);
    }

    public function withSlug(string $slug): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => $slug,
        ]);
    }

    public function withDomains(array $domains): static
    {
        return $this->afterCreating(function (Tenant $tenant) use ($domains) {
            foreach ($domains as $domain) {
                $tenant->domains()->create(['domain' => $domain]);
            }
        });
    }
}
