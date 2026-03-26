<?php

declare(strict_types=1);

use Modules\Staff\Domain\Enums\StaffRole;
use Modules\Staff\Domain\Models\TenantUser;

beforeEach(function () {
    seedStaffRolesAndPermissions();
});

describe('POST /api/v1/staff/auth/login', function () {

    it('authenticates active staff and returns user with roles', function () {
        $staff = TenantUser::factory()->create([
            'email' => 'receptionist@hotel.test',
            'password' => 'SecurePassword123!',
        ]);
        $staff->assignRole(StaffRole::Receptionist);

        $response = $this->postJson('/api/v1/staff/auth/login', [
            'email' => 'receptionist@hotel.test',
            'password' => 'SecurePassword123!',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Logged in successfully.'])
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'preferred_language',
                    'is_active',
                    'activated_at',
                    'last_login_at',
                    'roles',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('user.email', 'receptionist@hotel.test')
            ->assertJsonPath('user.is_active', true)
            ->assertJsonPath('user.roles', [StaffRole::Receptionist->value]);

        // Verify password is NOT in the response
        expect($response->json('user'))->not->toHaveKey('password');
    });

    it('updates last_login_at on successful login', function () {
        $staff = TenantUser::factory()->create([
            'email' => 'admin@hotel.test',
            'password' => 'SecurePassword123!',
            'last_login_at' => null,
        ]);

        $this->postJson('/api/v1/staff/auth/login', [
            'email' => 'admin@hotel.test',
            'password' => 'SecurePassword123!',
        ])->assertOk();

        $staff->refresh();
        expect($staff->last_login_at)->not->toBeNull();
    });

    it('rejects login for non-loginable accounts', function (string $scenario, callable $setup) {
        $setup();

        $response = $this->postJson('/api/v1/staff/auth/login', [
            'email' => 'test@hotel.test',
            'password' => 'SecurePassword123!',
        ]);

        $response->assertUnauthorized()
            ->assertJson(['error' => 'Invalid credentials or inactive account.']);

        $this->assertGuest('tenant');
    })->with([
        'wrong password' => [
            'wrong password',
            function () {
                TenantUser::factory()->create([
                    'email' => 'test@hotel.test',
                    'password' => 'DifferentPassword123!',
                ]);
            },
        ],
        'non-existent email' => [
            'non-existent email',
            function () {
                // No user created — email doesn't exist in database
            },
        ],
        'inactive (deactivated) account' => [
            'inactive (deactivated) account',
            function () {
                TenantUser::factory()->deactivated()->create([
                    'email' => 'test@hotel.test',
                    'password' => 'SecurePassword123!',
                ]);
            },
        ],
        'non-activated account (pending password setup)' => [
            'non-activated account (pending password setup)',
            function () {
                TenantUser::factory()->inactive()->create([
                    'email' => 'test@hotel.test',
                ]);
            },
        ],
    ]);

    it('validates required fields and constraints', function (string $field, array $payload) {
        $response = $this->postJson('/api/v1/staff/auth/login', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($field);
    })->with([
        'missing email' => ['email', ['password' => 'Password123!']],
        'missing password' => ['password', ['email' => 'test@hotel.test']],
        'invalid email format' => ['email', ['email' => 'not-an-email', 'password' => 'Password123!']],
        'email exceeds max length' => [
            'email',
            [
                'email' => str_repeat('a', 129) . '@example.com',
                'password' => 'Password123!',
            ],
        ],
    ]);

    it('authenticates staff only within current tenant context', function () {
        // Staff exists in tenant database but login is scoped to tenant context
        // This test verifies the tenant guard properly isolates authentication
        $staff = TenantUser::factory()->create([
            'email' => 'staff@hotel.test',
            'password' => 'SecurePassword123!',
        ]);
        $staff->assignRole(StaffRole::Receptionist);

        $response = $this->postJson('/api/v1/staff/auth/login', [
            'email' => 'staff@hotel.test',
            'password' => 'SecurePassword123!',
        ]);

        $response->assertOk();

        // Verify authentication is bound to tenant guard, not web guard
        $this->assertAuthenticated('tenant');
        $this->assertGuest('web');
    });
});
