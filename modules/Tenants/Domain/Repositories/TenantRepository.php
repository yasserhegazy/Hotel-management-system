<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\Repositories;

use Illuminate\Support\Str;
use Modules\Tenants\Domain\Models\Location;
use Modules\Tenants\Domain\Models\Tenant;

class TenantRepository
{
    public function create(array $data): Tenant
    {
        return Tenant::create($data);
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);

        return $tenant->fresh();
    }

    public function findByVerificationToken(string $tokenHash): ?Tenant
    {
        return Tenant::where('verification_token', $tokenHash)->first();
    }

    public function createLocation(array $data): Location
    {
        return Location::createFromCode($data);
    }

    public function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $count = 1;
        $originalSlug = $slug;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$count;
            $count++;
        }

        return $slug;
    }
}
