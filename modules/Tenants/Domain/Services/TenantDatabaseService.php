<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\Services;

use Modules\Tenants\Domain\Models\Tenant;
use Stancl\Tenancy\Exceptions\TenantDatabaseAlreadyExistsException;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;

class TenantDatabaseService
{
    /**
     * Create tenant-specific database and run migrations
     */
    public function create(Tenant $tenant): void
    {
        // Skip database creation in test environment (uses in-memory SQLite)
        if (app()->environment('testing')) {
            return;
        }

        // Create the tenant database using stancl/tenancy jobs
        // These jobs handle database creation and migrations
        try {
            dispatch_sync(new CreateDatabase($tenant));
        } catch (TenantDatabaseAlreadyExistsException $e) {
            // Database already exists - this is OK, proceed with migrations
            // This can happen if a previous registration attempt was interrupted
        }

        dispatch_sync(new MigrateDatabase($tenant));
    }
}
