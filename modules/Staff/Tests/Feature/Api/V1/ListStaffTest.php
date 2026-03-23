<?php

declare(strict_types=1);

use Modules\Staff\Domain\Enums\StaffRole;
use Modules\Staff\Domain\Models\TenantUser;

beforeEach(function () {
    seedStaffRolesAndPermissions();
});

describe('GET /api/v1/staff', function () {

    it('returns paginated list of staff members for authorized admin', function () {
        $admin = createAdminStaff();

        foreach (range(1, 3) as $i) {
            $staff = createTenantUser();
            $staff->assignRole(StaffRole::Receptionist);
        }

        $response = $this->actingAs($admin, 'tenant')
            ->getJson('/api/v1/staff');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
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
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ]);

        expect($response->json('data.0'))->not->toHaveKey('password');
    });

    it('supports pagination', function () {
        $admin = createAdminStaff();

        foreach (range(1, 16) as $i) {
            $staff = createTenantUser();
            $staff->assignRole(StaffRole::Receptionist);
        }

        $response = $this->actingAs($admin, 'tenant')
            ->getJson('/api/v1/staff?page=2&per_page=5');

        $response->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 5);
    });

    it('supports search by name, email, or phone', function (string $field, string $searchTerm, array $targetAttributes) {
        $admin = createAdminStaff();

        $target = createTenantUser($targetAttributes);
        $target->assignRole(StaffRole::Receptionist);

        $other = createTenantUser();
        $other->assignRole(StaffRole::Receptionist);

        $response = $this->actingAs($admin, 'tenant')
            ->getJson('/api/v1/staff?search='.urlencode($searchTerm));

        $response->assertOk();

        $emails = collect($response->json('data'))->pluck('email');

        expect($emails)->toContain($target->email);
    })->with([
        'by first name' => ['first_name', 'UniqueFirstName', ['first_name' => 'UniqueFirstName']],
        'by last name' => ['last_name', 'UniqueLastName', ['last_name' => 'UniqueLastName']],
        'by email' => ['email', 'searchable@hotel.test', ['email' => 'searchable@hotel.test']],
        'by phone' => ['phone', '+9876543210', ['phone' => '+9876543210']],
    ]);

    it('supports filtering by is_active status', function () {
        $admin = createAdminStaff();

        $activeStaff = createTenantUser(['first_name' => 'ActiveStaff']);
        $activeStaff->assignRole(StaffRole::Receptionist);

        $inactiveStaff = TenantUser::factory()->deactivated()->create(['first_name' => 'InactiveStaff']);
        $inactiveStaff->assignRole(StaffRole::Receptionist);

        $activeResponse = $this->actingAs($admin, 'tenant')
            ->getJson('/api/v1/staff?is_active=1');

        $activeResponse->assertOk();
        $activeEmails = collect($activeResponse->json('data'))->pluck('email');
        expect($activeEmails)->not->toContain($inactiveStaff->email);

        $inactiveResponse = $this->actingAs($admin, 'tenant')
            ->getJson('/api/v1/staff?is_active=0');

        $inactiveResponse->assertOk();
        $inactiveEmails = collect($inactiveResponse->json('data'))->pluck('email');
        expect($inactiveEmails)->toContain($inactiveStaff->email);
        expect($inactiveEmails)->not->toContain($admin->email);
    });

    it('supports filtering by role', function () {
        $admin = createAdminStaff();

        $receptionist = createTenantUser();
        $receptionist->assignRole(StaffRole::Receptionist);

        $housekeeper = createTenantUser();
        $housekeeper->assignRole(StaffRole::Housekeeper);

        $response = $this->actingAs($admin, 'tenant')
            ->getJson('/api/v1/staff?role=receptionist');

        $response->assertOk();

        $emails = collect($response->json('data'))->pluck('email');
        expect($emails)->toContain($receptionist->email);
        expect($emails)->not->toContain($housekeeper->email);
        expect($emails)->not->toContain($admin->email);
    });

    it('rejects unauthenticated request', function () {
        $this->getJson('/api/v1/staff')
            ->assertUnauthorized();

        $this->assertGuest('tenant');
    });

    it('rejects authenticated staff without manage_staff permission', function () {
        $staff = createTenantUser();
        $staff->assignRole(StaffRole::Receptionist);

        $this->actingAs($staff, 'tenant')
            ->getJson('/api/v1/staff')
            ->assertForbidden();
    });
});
