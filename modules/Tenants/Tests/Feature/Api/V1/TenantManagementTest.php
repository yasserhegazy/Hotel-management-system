<?php

use App\Models\User;

describe('PATCH /api/v1/hotels/{hotel_id}', function () {
    it('updates hotel profile as owner', function () {
        $owner = User::factory()->create();
        $tenant = createTenant(['owner_id' => $owner->id]);

        $payload = [
            'name' => 'Updated Hotel Name',
            'email' => 'updated@hotel.com',
            'location' => [
                'country_code' => 'GB',
                'city' => 'London',
            ],
        ];

        $response = $this->actingAs($owner)
            ->patchJson("/api/v1/hotels/{$tenant->id}", $payload);

        $response->assertOk()
            ->assertJson(['message' => 'Tenant updated successfully.'])
            ->assertJsonStructure(['tenant']);
    });

    it('allows partial updates', function () {
        $owner = User::factory()->create();
        $tenant = createTenant(['owner_id' => $owner->id, 'name' => 'Old Name']);

        $response = $this->actingAs($owner)
            ->patchJson("/api/v1/hotels/{$tenant->id}", ['name' => 'New Name']);

        $response->assertOk();
    });

    it('requires authentication', function () {
        $tenant = createTenant();

        $response = $this->patchJson("/api/v1/hotels/{$tenant->id}", ['name' => 'New']);

        $response->assertUnauthorized();
    });

    it('prevents unauthorized users from updating', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $tenant = createTenant(['owner_id' => $owner->id]);

        $response = $this->actingAs($otherUser)
            ->patchJson("/api/v1/hotels/{$tenant->id}", ['name' => 'Hacked']);

        $response->assertForbidden();
    });

    it('returns not found for non-existent hotel', function () {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)
            ->patchJson('/api/v1/hotels/99999', ['name' => 'Test']);

        $response->assertNotFound();
    });

    it('validates field constraints', function ($field, $payload) {
        $owner = User::factory()->create();
        $tenant = createTenant(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)
            ->patchJson("/api/v1/hotels/{$tenant->id}", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($field);
    })->with([
        'invalid email' => ['email', ['email' => 'invalid-format']],
        'name too long' => ['name', ['name' => str_repeat('a', 129)]],
        'missing city in location' => ['location.city', [
            'location' => ['country_code' => 'US'],
        ]],
    ]);
});

// Helper function
function createTenant(array $attributes = [])
{
    $location = \Modules\Tenants\Domain\Models\Location::factory()->create();

    return \Modules\Tenants\Domain\Models\Tenant::factory()->create(array_merge([
        'location_id' => $location->id,
    ], $attributes));
}
