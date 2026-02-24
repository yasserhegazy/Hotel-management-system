<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\Services;

use Illuminate\Http\UploadedFile;
use Modules\Tenants\Domain\Models\Tenant;
use Modules\Tenants\Domain\Repositories\TenantRepository;

class UpdateTenantService
{
    public function __construct(private TenantRepository $tenantRepository) {}

    public function handle(
        Tenant $tenant,
        array $data,
        ?UploadedFile $profileImage = null,
        bool $removeProfileImage = false,
    ): Tenant {
        // Handle location
        if (isset($data['location'])) {
            $locationData = $data['location'];
            unset($data['location']);

            if ($tenant->location) {
                $this->tenantRepository->updateLocation($tenant->location, $locationData);
            } else {
                $location = $this->tenantRepository->createLocation($locationData);
                $data['location_id'] = $location->id;
            }
        }

        // Handle profile image
        if ($removeProfileImage) {
            $this->tenantRepository->removeProfileImage($tenant);
        } elseif ($profileImage) {
            $this->tenantRepository->updateProfileImage($tenant, $profileImage);
        }

        // Update tenant fields
        if (! empty($data)) {
            $this->tenantRepository->update($tenant, $data);
        }

        return $tenant->fresh(['location']);
    }
}
