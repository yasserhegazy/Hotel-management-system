<?php

declare(strict_types=1);

use Modules\Staff\Domain\Enums\StaffRole;
use Modules\Staff\Domain\Models\TenantUser;
use Modules\Tenants\Domain\Models\Location;
use Modules\Tenants\Domain\Models\Tenant;

beforeEach(function () {
    seedStaffRolesAndPermissions();

    $location = Location::factory()->create();
    $this->tenant = Tenant::factory()->create(['location_id' => $location->id]);

    // Set tenant in tenancy singleton (SQLite can't switch DBs)
    $tenancy = app(\Stancl\Tenancy\Tenancy::class);
    $tenancy->tenant = $this->tenant;
});

describe('Staff auth persistence', function () {

    it('returns tenant_id in login response for frontend storage', function () {
        $staff = TenantUser::factory()->create([
            'email' => 'session@hotel.test',
            'password' => 'SecurePassword123!',
        ]);
        $staff->assignRole(StaffRole::Receptionist);

        $response = $this->withHeaders(['Origin' => 'http://localhost'])
            ->postJson('/api/v1/staff/auth/login', [
                'email' => 'session@hotel.test',
                'password' => 'SecurePassword123!',
            ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'user', 'tenant_id'])
            ->assertJsonPath('tenant_id', $this->tenant->getKey());
    });

    it('does not return tenant_id on failed login', function () {
        $response = $this->withHeaders(['Origin' => 'http://localhost'])
            ->postJson('/api/v1/staff/auth/login', [
                'email' => 'nonexistent@hotel.test',
                'password' => 'WrongPassword123!',
            ]);

        $response->assertUnauthorized()
            ->assertJsonMissing(['tenant_id']);
    });

    it('logs out authenticated staff', function () {
        $staff = TenantUser::factory()->create([
            'email' => 'logout@hotel.test',
            'password' => 'SecurePassword123!',
        ]);
        $staff->assignRole(StaffRole::Receptionist);

        $this->actingAs($staff, 'tenant')
            ->postJson('/api/v1/staff/auth/logout')
            ->assertNoContent();

        $this->assertGuest('tenant');
    });

    it('returns 401 when accessing protected route without authentication', function () {
        $this->postJson('/api/v1/staff/auth/logout')
            ->assertUnauthorized();
    });
});
