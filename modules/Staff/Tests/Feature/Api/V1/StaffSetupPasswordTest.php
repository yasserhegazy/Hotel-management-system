<?php

declare(strict_types=1);

use Modules\Staff\Domain\Models\TenantUser;

beforeEach(function () {
    seedStaffRolesAndPermissions();
});

describe('POST /api/v1/staff/setup-password', function () {

    it('sets password and activates account with valid token', function () {
        $staff = createInactiveStaff(['email' => 'newstaff@hotel.test']);
        $plainToken = $staff->plain_setup_token;

        $response = $this->postJson('/api/v1/staff/setup-password', [
            'token' => $plainToken,
            'email' => 'newstaff@hotel.test',
            'password' => 'MyNewSecure123!',
            'password_confirmation' => 'MyNewSecure123!',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Password set successfully. Your account is now active.',
            ]);

        $staff->refresh();

        expect($staff->is_active)->toBeTrue()
            ->and($staff->activated_at)->not->toBeNull()
            ->and($staff->setup_token)->toBeNull()
            ->and($staff->setup_token_expires_at)->toBeNull();

        // Verify password was hashed correctly
        expect(password_verify('MyNewSecure123!', $staff->password))->toBeTrue();
    });

    it('token is single-use — reuse returns error', function () {
        $staff = createInactiveStaff(['email' => 'oneuse@hotel.test']);
        $plainToken = $staff->plain_setup_token;

        $payload = [
            'token' => $plainToken,
            'email' => 'oneuse@hotel.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        // First use — succeeds
        $this->postJson('/api/v1/staff/setup-password', $payload)->assertOk();

        // Second use — fails (token consumed)
        $this->postJson('/api/v1/staff/setup-password', $payload)
            ->assertStatus(400)
            ->assertJson(['error' => 'Invalid or expired setup token.']);
    });

    it('rejects expired setup token', function () {
        $staff = TenantUser::factory()->expiredToken()->create([
            'email' => 'expired@hotel.test',
        ]);

        $this->postJson('/api/v1/staff/setup-password', [
            'token' => $staff->plain_setup_token,
            'email' => 'expired@hotel.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertStatus(400)
            ->assertJson(['error' => 'Invalid or expired setup token.']);
    });

    it('rejects invalid token scenarios', function (string $scenario, callable $setup, array $payload) {
        $setup();

        $response = $this->postJson('/api/v1/staff/setup-password', $payload);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid or expired setup token.']);
    })->with([
        'completely wrong token' => [
            'completely wrong token',
            function () {
                TenantUser::factory()->inactive()->create(['email' => 'staff@hotel.test']);
            },
            [
                'token' => 'this-token-does-not-exist-at-all',
                'email' => 'staff@hotel.test',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
            ],
        ],
        'email does not match token owner' => [
            'email does not match token owner',
            function () {
                TenantUser::factory()->inactive()->create(['email' => 'real@hotel.test']);
            },
            [
                'token' => bin2hex(random_bytes(32)),
                'email' => 'different@hotel.test',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
            ],
        ],
    ]);

    it('rejects setup for already activated account', function () {
        // Active user = already has password and is_active=true
        $activeStaff = TenantUser::factory()->create([
            'email' => 'active@hotel.test',
            'is_active' => true,
            'activated_at' => now(),
        ]);

        // Try to set password for an already-active account
        $response = $this->postJson('/api/v1/staff/setup-password', [
            'token' => bin2hex(random_bytes(32)),
            'email' => 'active@hotel.test',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        // Returns generic 400 for security (doesn't reveal account state)
        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid or expired setup token.']);
    });

    it('validates required fields and constraints', function (string $field, array $payload) {
        $response = $this->postJson('/api/v1/staff/setup-password', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($field);
    })->with([
        'missing token' => ['token', [
            'email' => 'test@hotel.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]],
        'missing email' => ['email', [
            'token' => 'some-token',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]],
        'missing password' => ['password', [
            'token' => 'some-token',
            'email' => 'test@hotel.test',
        ]],
        'password too short' => ['password', [
            'token' => 'some-token',
            'email' => 'test@hotel.test',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]],
        'password confirmation mismatch' => ['password', [
            'token' => 'some-token',
            'email' => 'test@hotel.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'DifferentPass456!',
        ]],
    ]);
});
