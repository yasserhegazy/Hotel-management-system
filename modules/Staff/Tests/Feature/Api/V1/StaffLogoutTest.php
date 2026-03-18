<?php

declare(strict_types=1);

use Modules\Staff\Domain\Enums\StaffRole;
use Modules\Staff\Domain\Models\TenantUser;

beforeEach(function () {
    seedStaffRolesAndPermissions();
});

describe('POST /api/v1/staff/auth/logout', function () {

    it('logs out authenticated staff and invalidates session', function () {
        $staff = TenantUser::factory()->create();
        $staff->assignRole(StaffRole::Receptionist);

        $this->actingAs($staff, 'tenant')
            ->postJson('/api/v1/staff/auth/logout')
            ->assertNoContent();

        $this->assertGuest('tenant');
    });

    it('rejects unauthenticated logout', function () {
        $this->postJson('/api/v1/staff/auth/logout')
            ->assertUnauthorized();
    });
});
