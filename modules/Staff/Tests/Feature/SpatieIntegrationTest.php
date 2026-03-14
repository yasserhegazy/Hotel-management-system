<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Modules\Staff\Domain\Enums\StaffPermission;
use Modules\Staff\Domain\Enums\StaffRole;
use Modules\Staff\Domain\Models\TenantUser;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

describe('Spatie Permission Integration', function () {

    it('has all permission tables', function () {
        $tables = ['roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions'];

        foreach ($tables as $table) {
            expect(Schema::hasTable($table))->toBeTrue("Missing table: {$table}");
        }
    });

    it('seeds all roles with tenant guard', function () {
        seedStaffRolesAndPermissions();

        foreach (StaffRole::cases() as $role) {
            $dbRole = Role::where('name', $role->value)->where('guard_name', 'tenant')->first();
            expect($dbRole)->not->toBeNull("Missing role: {$role->value}");
        }

        expect(Role::where('guard_name', 'tenant')->count())->toBe(count(StaffRole::cases()));
    });

    it('seeds all permissions with tenant guard', function () {
        seedStaffRolesAndPermissions();

        foreach (StaffPermission::cases() as $permission) {
            $dbPerm = Permission::where('name', $permission->value)->where('guard_name', 'tenant')->first();
            expect($dbPerm)->not->toBeNull("Missing permission: {$permission->value}");
        }

        expect(Permission::where('guard_name', 'tenant')->count())->toBe(count(StaffPermission::cases()));
    });

    it('assigns correct permissions to hotel_admin role', function () {
        seedStaffRolesAndPermissions();

        $role = Role::findByName(StaffRole::HotelAdmin->value, 'tenant');
        $permissionNames = $role->permissions->pluck('name')->sort()->values();
        $allPermissions = collect(StaffPermission::cases())->map->value->sort()->values();

        expect($permissionNames->all())->toBe($allPermissions->all());
    });

    it('assigns correct permissions to receptionist role', function () {
        seedStaffRolesAndPermissions();

        $role = Role::findByName(StaffRole::Receptionist->value, 'tenant');
        $permissionNames = $role->permissions->pluck('name')->sort()->values()->all();

        expect($permissionNames)->toBe([
            StaffPermission::CheckIn->value,
            StaffPermission::CheckOut->value,
            StaffPermission::ManageBookings->value,
            StaffPermission::ViewStaff->value,
            StaffPermission::ViewUnits->value,
        ]);
    });

    it('assigns correct permissions to housekeeper role', function () {
        seedStaffRolesAndPermissions();

        $role = Role::findByName(StaffRole::Housekeeper->value, 'tenant');
        $permissionNames = $role->permissions->pluck('name')->sort()->values()->all();

        expect($permissionNames)->toBe([
            StaffPermission::UpdateUnitStatus->value,
            StaffPermission::ViewUnits->value,
        ]);
    });

    it('allows TenantUser to be assigned a role', function () {
        seedStaffRolesAndPermissions();

        $user = TenantUser::factory()->create();
        $user->assignRole(StaffRole::HotelAdmin);

        expect($user->hasRole(StaffRole::HotelAdmin))->toBeTrue();
    });

    it('resolves permissions through role inheritance', function () {
        seedStaffRolesAndPermissions();

        $user = TenantUser::factory()->create();
        $user->assignRole(StaffRole::Receptionist);

        expect($user->hasPermissionTo(StaffPermission::ManageBookings->value))->toBeTrue()
            ->and($user->hasPermissionTo(StaffPermission::CheckIn->value))->toBeTrue()
            ->and($user->hasPermissionTo(StaffPermission::ManageStaff->value))->toBeFalse();
    });

    it('creates admin staff via helper with hotel_admin role', function () {
        seedStaffRolesAndPermissions();

        $user = createAdminStaff();

        expect($user->hasRole(StaffRole::HotelAdmin))->toBeTrue()
            ->and($user->hasPermissionTo(StaffPermission::ManageStaff->value))->toBeTrue();
    });

    it('is idempotent when seeder runs multiple times', function () {
        seedStaffRolesAndPermissions();
        seedStaffRolesAndPermissions();

        expect(Role::where('guard_name', 'tenant')->count())->toBe(count(StaffRole::cases()))
            ->and(Permission::where('guard_name', 'tenant')->count())->toBe(count(StaffPermission::cases()));
    });
});
