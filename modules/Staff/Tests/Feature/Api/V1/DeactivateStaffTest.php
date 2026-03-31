<?php

declare(strict_types=1);

use Modules\Staff\Domain\Enums\StaffRole;

beforeEach(function () {
    seedStaffRolesAndPermissions();
});

describe('DELETE /api/v1/staff/{staff_id}', function () {

    it('deactivates an active staff member', function () {
        $admin = createAdminStaff();
        $target = createTenantUser(['is_active' => true]);

        $response = $this->actingAs($admin, 'tenant')
            ->deleteJson("/api/v1/staff/{$target->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Staff member deactivated successfully.']);

        $target->refresh();
        expect($target->is_active)->toBeFalse();
    });

    it('prevents deactivated staff from logging in', function () {
        $admin = createAdminStaff();
        $target = createTenantUser([
            'email' => 'willbe@deactivated.test',
            'password' => 'SecurePassword123!',
        ]);

        $this->actingAs($admin, 'tenant')
            ->deleteJson("/api/v1/staff/{$target->id}")
            ->assertOk();

        $this->postJson('/api/v1/staff/auth/login', [
            'email' => 'willbe@deactivated.test',
            'password' => 'SecurePassword123!',
        ])->assertUnauthorized();
    });

    it('prevents admin from deactivating themselves', function () {
        $admin = createAdminStaff();

        $response = $this->actingAs($admin, 'tenant')
            ->deleteJson("/api/v1/staff/{$admin->id}");

        $response->assertForbidden()
            ->assertJson(['error' => 'You cannot deactivate your own account.']);
    });

    it('returns 404 for non-existent staff member', function () {
        $admin = createAdminStaff();

        $this->actingAs($admin, 'tenant')
            ->deleteJson('/api/v1/staff/99999')
            ->assertNotFound();
    });

    it('rejects non-authorized actor', function (string $scenario, Closure $actorSetup, int $expectedStatus) {
        $actor = $actorSetup();

        $target = createTenantUser();

        $request = $actor
            ? $this->actingAs($actor, 'tenant')
            : $this;

        $request->deleteJson("/api/v1/staff/{$target->id}")
            ->assertStatus($expectedStatus);
    })->with([
        'unauthenticated' => [
            'unauthenticated',
            function () {
                return null;
            },
            401,
        ],
        'without manage_staff permission' => [
            'without manage_staff permission',
            function () {
                $staff = createTenantUser();
                $staff->assignRole(StaffRole::Receptionist);

                return $staff;
            },
            403,
        ],
    ]);
});
