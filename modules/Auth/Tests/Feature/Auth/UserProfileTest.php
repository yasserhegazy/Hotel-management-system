<?php

use App\Models\User;

describe('GET /auth/me', function () {
    it('returns authenticated user profile', function () {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/auth/me');

        $response->assertOk()
            ->assertJson([
                'id' => $user->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
            ]);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/auth/me');

        $response->assertUnauthorized();
    });

    it('does not expose password', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/auth/me');

        expect($response->json())->not->toHaveKey('password');
    });

    it('returns only authenticated users data', function () {
        $user1 = User::factory()->create(['email' => 'user1@test.com']);
        $user2 = User::factory()->create(['email' => 'user2@test.com']);

        $response = $this->actingAs($user1)
            ->getJson('/auth/me');

        $response->assertOk()
            ->assertJson(['email' => 'user1@test.com']);

        expect($response->json('email'))->not->toBe('user2@test.com');
    });
});
