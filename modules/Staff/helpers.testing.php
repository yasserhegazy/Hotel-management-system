<?php

declare(strict_types=1);

use Modules\Staff\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\Staff\Domain\Enums\StaffRole;
use Modules\Staff\Domain\Models\TenantUser;

if (! function_exists('createTenantUser')) {
    function createTenantUser(array $attributes = []): TenantUser
    {
        return TenantUser::factory()->create($attributes);
    }
}

if (! function_exists('createInactiveStaff')) {
    function createInactiveStaff(array $attributes = []): TenantUser
    {
        return TenantUser::factory()->inactive()->create($attributes);
    }
}

if (! function_exists('createAdminStaff')) {
    /**
     * Creates an active TenantUser with hotel_admin role.
     * Requires seedStaffRolesAndPermissions() to be called first.
     */
    function createAdminStaff(array $attributes = []): TenantUser
    {
        $user = TenantUser::factory()->create($attributes);
        $user->assignRole(StaffRole::HotelAdmin);

        return $user->refresh();
    }
}

if (! function_exists('seedStaffRolesAndPermissions')) {
    /**
     * Seeds default roles and permissions for the tenant guard.
     */
    function seedStaffRolesAndPermissions(): void
    {
        (new RolesAndPermissionsSeeder)->run();
    }
}
