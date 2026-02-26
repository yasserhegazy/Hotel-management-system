<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\Repositories;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function updateLocation(Location $location, array $data): Location
    {
        $data['country_code'] = strtoupper((string) $data['country_code']);

        $countries = new \PragmaRX\Countries\Package\Countries;
        $country = $countries->where('cca2', $data['country_code'])->first();

        $data['country_name'] = $country?->name?->common ?? $data['country_code'];

        if (empty($data['timezone']) && $country) {
            $timezones = $country->timezones ?? null;
            $data['timezone'] = $timezones ? collect($timezones)->first() : null;
        }

        $location->update($data);

        return $location->fresh();
    }

    public function updateProfileImage(Tenant $tenant, UploadedFile $image): string
    {
        $this->removeProfileImage($tenant);
        $path = $image->store("tenants/{$tenant->id}", 'public');
        $tenant->update(['profile_image_path' => $path]);

        return $path;
    }

    public function removeProfileImage(Tenant $tenant): void
    {
        if ($tenant->profile_image_path) {
            Storage::disk('public')->delete($tenant->profile_image_path);
            $tenant->update(['profile_image_path' => null]);
        }
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
