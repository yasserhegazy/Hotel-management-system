<?php

use App\Models\User;

describe('POST /api/auth/logout', function () {
    it('logs out authenticated user', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/auth/logout');

        $response->assertNoContent();
        $this->assertGuest();
    });

    it('requires authentication', function () {
        $response = $this->postJson('/api/auth/logout');

        $response->assertUnauthorized();
    });

    it('prevents access to protected routes after logout', function () {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/auth/logout');

        $response = $this->getJson('/api/auth/me');
        $response->assertUnauthorized();
    });
});

describe('GET /sanctum/csrf-cookie', function () {
    it('returns CSRF cookie', function () {
        $response = $this->getJson('/sanctum/csrf-cookie');

        $response->assertNoContent()
            ->assertCookie('XSRF-TOKEN');
    });

    it('does not require authentication', function () {
        $response = $this->getJson('/sanctum/csrf-cookie');

        $response->assertNoContent();
    });
});
