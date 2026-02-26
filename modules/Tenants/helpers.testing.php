<?php

use Modules\Tenants\Domain\Models\Location;
use Modules\Tenants\Domain\Models\Tenant;

if (! function_exists('createPendingTenant')) {
    /**
     * Create a tenant in pending_verification status for testing
     */
    function createPendingTenant(array $attributes = []): Tenant
    {
        $location = Location::factory()->create();

        $plainToken = Tenant::generatePlainToken();

        $tenant = Tenant::factory()->pendingVerification()->create(array_merge([
            'location_id' => $location->id,
            'verification_token' => Tenant::hashToken($plainToken),
            'verification_expires_at' => now()->addHours(24),
        ], $attributes));

        // Expose plain token as a transient property for test assertions
        $tenant->verification_token = $plainToken;

        return $tenant;
    }
}

if (! function_exists('createVerifiedPendingTenant')) {
    /**
     * Create a tenant in verified status (ready for password setup) for testing
     */
    function createVerifiedPendingTenant(array $attributes = []): Tenant
    {
        $location = Location::factory()->create();

        $plainToken = Tenant::generatePlainToken();

        $tenant = Tenant::factory()->verified()->create(array_merge([
            'location_id' => $location->id,
            'verification_token' => Tenant::hashToken($plainToken),
            'verification_expires_at' => now()->addHours(24),
        ], $attributes));

        // Expose plain token as a transient property for test assertions
        $tenant->verification_token = $plainToken;

        return $tenant;
    }
}
