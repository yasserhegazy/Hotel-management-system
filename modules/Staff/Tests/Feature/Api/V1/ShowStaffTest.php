<?php

declare(strict_types=1);

use Modules\Staff\Domain\Enums\StaffRole;

beforeEach(function () {
    seedStaffRolesAndPermissions();
});

describe('GET /api/v1/staff/{staff_id}', function () {

    it('returns single staff member details for authorized admin', function () {
        $admin = createAdminStaff();

        $target = createTenantUser(['email' => 'target@hotel.test']);
        $target->assignRole(StaffRole::Receptionist);

        $response = $this->actingAs($admin, 'tenant')
            ->getJson("/api/v1/staff/{$target->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
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
            ->assertJsonPath('data.email', 'target@hotel.test')
            ->assertJsonPath('data.roles', ['receptionist']);

        expect($response->json('data'))->not->toHaveKey('password');
    });

    it('returns 404 for non-existent staff member', function () {
        $admin = createAdminStaff();

        $this->actingAs($admin, 'tenant')
            ->getJson('/api/v1/staff/99999')
            ->assertNotFound();
    });

    it('rejects staff without manage_staff permission', function (string $scenario, Closure $actorSetup, int $expectedStatus) {
        $actor = $actorSetup();

        $target = createTenantUser();
        $target->assignRole(StaffRole::Receptionist);

        $request = $actor
            ? $this->actingAs($actor, 'tenant')
            : $this;

        $request->getJson("/api/v1/staff/{$target->id}")
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
