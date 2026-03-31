<?php

declare(strict_types=1);

use Modules\Staff\Domain\Enums\StaffRole;

beforeEach(function () {
    seedStaffRolesAndPermissions();
});

describe('PATCH /api/v1/staff/{staff_id}', function () {

    it('updates staff identity fields', function () {
        $admin = createAdminStaff();

        $target = createTenantUser(['email' => 'old@hotel.test']);

        $payload = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'new@hotel.test',
            'phone' => '+9999999999',
        ];

        $response = $this->actingAs($admin, 'tenant')
            ->patchJson("/api/v1/staff/{$target->id}", $payload);

        $response->assertOk()
            ->assertJsonPath('data.first_name', 'Updated')
            ->assertJsonPath('data.email', 'new@hotel.test');

        $this->assertDatabaseHas('tenant_users', [
            'id' => $target->id,
            'email' => 'new@hotel.test',
            'first_name' => 'Updated',
        ]);
    });

    it('syncs roles when roles are provided', function () {
        $admin = createAdminStaff();

        $target = createTenantUser();
        $target->assignRole(StaffRole::Receptionist);

        $this->actingAs($admin, 'tenant')
            ->patchJson("/api/v1/staff/{$target->id}", [
                'roles' => [StaffRole::Housekeeper->value],
            ])
            ->assertOk();

        $target->refresh();
        expect($target->hasRole(StaffRole::Housekeeper))->toBeTrue();
        expect($target->hasRole(StaffRole::Receptionist))->toBeFalse();
    });

    it('rejects invalid role names', function (string $scenario, array $payload) {
        $admin = createAdminStaff();

        $target = createTenantUser();

        $this->actingAs($admin, 'tenant')
            ->patchJson("/api/v1/staff/{$target->id}", $payload)
            ->assertStatus(422);
    })->with([
        'non-existent role' => ['non-existent role', ['roles' => ['fake_role']]],
        'empty roles array' => ['empty roles array', ['roles' => []]],
    ]);

    it('rejects duplicate email conflict', function () {
        $admin = createAdminStaff();

        createTenantUser(['email' => 'a@hotel.test']);
        $staffB = createTenantUser(['email' => 'b@hotel.test']);

        $this->actingAs($admin, 'tenant')
            ->patchJson("/api/v1/staff/{$staffB->id}", [
                'email' => 'a@hotel.test',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    });

    it('returns 404 for non-existent staff member', function () {
        $admin = createAdminStaff();

        $this->actingAs($admin, 'tenant')
            ->patchJson('/api/v1/staff/99999', ['first_name' => 'Ghost'])
            ->assertNotFound();
    });

    it('rejects non-authorized actor', function (string $scenario, Closure $actorSetup, int $expectedStatus) {
        $actor = $actorSetup();

        $target = createTenantUser();

        $request = $actor
            ? $this->actingAs($actor, 'tenant')
            : $this;

        $request->patchJson("/api/v1/staff/{$target->id}", ['first_name' => 'Hacked'])
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
