<?php

declare(strict_types=1);

namespace Modules\Staff\Database\Seeders;

use Modules\Staff\Domain\Enums\StaffPermission;
use Modules\Staff\Domain\Enums\StaffRole;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder
{
    private const GUARD = 'tenant';

    private const ROLE_PERMISSIONS = [
        StaffRole::HotelAdmin->value => '*',
        StaffRole::Manager->value => [
            StaffPermission::ManageStaff,
            StaffPermission::ViewStaff,
            StaffPermission::ManageBookings,
            StaffPermission::ManageUnits,
            StaffPermission::ViewUnits,
            StaffPermission::UpdateUnitStatus,
            StaffPermission::CheckIn,
            StaffPermission::CheckOut,
            StaffPermission::ViewReports,
        ],
        StaffRole::Receptionist->value => [
            StaffPermission::ViewStaff,
            StaffPermission::ManageBookings,
            StaffPermission::CheckIn,
            StaffPermission::CheckOut,
            StaffPermission::ViewUnits,
        ],
        StaffRole::Housekeeper->value => [
            StaffPermission::UpdateUnitStatus,
            StaffPermission::ViewUnits,
        ],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (StaffPermission::cases() as $permission) {
            Permission::findOrCreate($permission->value, self::GUARD);
        }

        $allPermissions = Permission::where('guard_name', self::GUARD)->get();

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, self::GUARD);

            if ($permissions === '*') {
                $role->syncPermissions($allPermissions);
            } else {
                $role->syncPermissions(
                    collect($permissions)->map(fn (StaffPermission $p) => $p->value)->all()
                );
            }
        }
    }
}
