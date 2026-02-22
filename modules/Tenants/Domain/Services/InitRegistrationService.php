<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\Services;

use Illuminate\Support\Facades\Mail;
use Modules\Tenants\Domain\DTOs\InitRegistrationDTO;
use Modules\Tenants\Domain\Models\Tenant;
use Modules\Tenants\Domain\Repositories\TenantRepository;
use Modules\Tenants\Mail\TenantVerificationMail;

class InitRegistrationService
{
    public function __construct(
        private TenantRepository $tenantRepository,
    ) {}

    public function handle(InitRegistrationDTO $dto): array
    {
        $location = $this->tenantRepository->createLocation($dto->location->toArray());

        $plainToken = Tenant::generatePlainToken();

        $tenant = $this->tenantRepository->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'slug' => $this->tenantRepository->generateUniqueSlug($dto->name),
            'location_id' => $location->id,
            'status' => Tenant::STATUS_PENDING_VERIFICATION,
            'email_verified_at' => null,
            'owner_id' => null,
            'verification_token' => Tenant::hashToken($plainToken),
            'verification_expires_at' => now()->addHours(24),
        ]);

        Mail::to($tenant->email)->queue(new TenantVerificationMail($tenant, $plainToken));

        return [
            'tenant' => $tenant,
        ];
    }
}
