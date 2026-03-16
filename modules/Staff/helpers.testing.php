<?php

declare(strict_types=1);

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
        $user->assignRole('hotel_admin');

        return $user->refresh();
    }
}

if (! function_exists('seedStaffRolesAndPermissions')) {
    /**
     * Stub — full implementation in Issue 2 after Spatie is installed.
     * Seeds default roles and permissions for the tenant guard.
     */
    function seedStaffRolesAndPermissions(): void
    {
        // Will be implemented in Issue 2 with Spatie + ENUMs
    }
}
