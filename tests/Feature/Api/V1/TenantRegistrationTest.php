<?php

use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

describe('POST /api/v1/hotels/init-register', function () {
    it('creates pending tenant and sends verification email with valid data', function () {
        $payload = [
            'name' => 'Grand Hotel Plaza',
            'email' => 'contact@grandhotelplaza.com',
            'phone' => '+1234567890',
            'location' => [
                'country_code' => 'US',
                'city' => 'Los Angeles',
                'address_line' => '123 Main Street',
                'postal_code' => '90001',
            ],
        ];

        $response = $this->postJson('/api/v1/hotels/init-register', $payload);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Verification email sent to hotel email.'])
            ->assertJsonStructure(['message', 'tenant_id']);

        expect($response->json('tenant_id'))->toBeInt();
        
        Mail::assertSent(fn($mail) => $mail->hasTo('contact@grandhotelplaza.com'));
    });

    it('validates required fields', function ($field, $payload) {
        $response = $this->postJson('/api/v1/hotels/init-register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($field);
    })->with([
        'missing name' => ['name', [
            'email' => 'test@hotel.com',
            'location' => ['country_code' => 'US', 'city' => 'LA'],
        ]],
        'missing email' => ['email', [
            'name' => 'Test Hotel',
            'location' => ['country_code' => 'US', 'city' => 'LA'],
        ]],
        'missing location' => ['location', [
            'name' => 'Test Hotel',
            'email' => 'test@hotel.com',
        ]],
        'missing country_code' => ['location.country_code', [
            'name' => 'Test Hotel',
            'email' => 'test@hotel.com',
            'location' => ['city' => 'LA'],
        ]],
        'missing city' => ['location.city', [
            'name' => 'Test Hotel',
            'email' => 'test@hotel.com',
            'location' => ['country_code' => 'US'],
        ]],
    ]);

    it('validates field formats and constraints', function ($field, $payload) {
        $response = $this->postJson('/api/v1/hotels/init-register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($field);
    })->with([
        'invalid email' => ['email', [
            'name' => 'Test',
            'email' => 'invalid-email',
            'location' => ['country_code' => 'US', 'city' => 'LA'],
        ]],
        'name too long' => ['name', [
            'name' => str_repeat('a', 129),
            'email' => 'test@hotel.com',
            'location' => ['country_code' => 'US', 'city' => 'LA'],
        ]],
        'country_code too long' => ['location.country_code', [
            'name' => 'Test',
            'email' => 'test@hotel.com',
            'location' => ['country_code' => 'TOOLONG', 'city' => 'LA'],
        ]],
    ]);

    it('prevents duplicate email registration', function () {
        $payload = [
            'name' => 'Test Hotel',
            'email' => 'duplicate@hotel.com',
            'location' => ['country_code' => 'US', 'city' => 'LA'],
        ];

        $this->postJson('/api/v1/hotels/init-register', $payload)->assertStatus(201);
        $response = $this->postJson('/api/v1/hotels/init-register', $payload);

        $response->assertStatus(409)->assertJsonStructure(['error']);
    });
});

describe('GET /api/v1/hotels/verify/{token}', function () {
    it('verifies valid token and updates tenant status', function () {
        // Create pending tenant - implementation will depend on your Tenant model
        // This is a placeholder that you'll need to implement
        $tenant = createPendingTenant([
            'email' => 'hotel@example.com',
            'status' => 'pending_verification',
        ]);
        $token = $tenant->verification_token;

        $response = $this->getJson("/api/v1/hotels/verify/{$token}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Token verified. Proceed to set password.',
                'tenant_id' => $tenant->id,
            ]);
    });

    it('rejects invalid or expired tokens', function ($token, $status) {
        $response = $this->getJson("/api/v1/hotels/verify/{$token}");

        $response->assertStatus($status)
            ->assertJsonStructure(['error']);
    })->with([
        'invalid token' => ['invalid-token-xyz', 400],
        'non-existent token' => ['non-existent-token', 404],
    ]);
});

describe('POST /api/v1/hotels/set-password', function () {
    it('creates owner, activates tenant and establishes session', function () {
        $tenant = createVerifiedPendingTenant(['email' => 'hotel@example.com']);

        $payload = [
            'token' => $tenant->verification_token,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        $response = $this->postJson('/api/v1/hotels/set-password', $payload);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Owner account created and tenant activated.'])
            ->assertJsonStructure([
                'tenant' => ['tenant_id', 'status', 'owner_id'],
                'user',
            ]);

        $this->assertAuthenticated();
    });

    it('validates required fields', function ($field, $payload) {
        $response = $this->postJson('/api/v1/hotels/set-password', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($field);
    })->with([
        'missing token' => ['token', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]],
        'missing first_name' => ['first_name', [
            'token' => 'token',
            'last_name' => 'Doe',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]],
        'password too short' => ['password', [
            'token' => 'token',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]],
        'password mismatch' => ['password', [
            'token' => 'token',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'Different123!',
        ]],
    ]);

    it('rejects invalid or expired tokens', function () {
        $payload = [
            'token' => 'invalid-token',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        $response = $this->postJson('/api/v1/hotels/set-password', $payload);

        $response->assertStatus(400)
            ->assertJsonStructure(['error']);
    });

    it('prevents duplicate owner creation', function () {
        $tenant = createVerifiedPendingTenant(['email' => 'hotel@example.com']);
        \App\Models\User::factory()->create(['email' => 'hotel@example.com']);

        $payload = [
            'token' => $tenant->verification_token,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        $response = $this->postJson('/api/v1/hotels/set-password', $payload);

        $response->assertStatus(409)->assertJsonStructure(['error']);
    });
});
