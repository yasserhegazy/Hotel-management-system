<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\Services;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Modules\Tenants\Domain\Exceptions\TenantAlreadyVerifiedException;
use Modules\Tenants\Domain\Models\Tenant;
use Modules\Tenants\Domain\Repositories\TenantRepository;

class VerifyEmailService
{
    public function __construct(
        private TenantRepository $tenantRepository,
    ) {}

    public function handle(string $plainToken): array
    {
        $tenant = $this->tenantRepository->findByVerificationToken(
            Tenant::hashToken($plainToken)
        );

        if (! $tenant) {
            throw new ModelNotFoundException('Token not found.');
        }

        if (! $tenant->verification_expires_at || $tenant->verification_expires_at->isPast()) {
            throw new InvalidArgumentException('Invalid or expired token.');
        }

        if ($tenant->hasVerifiedEmail()) {
            throw new TenantAlreadyVerifiedException;
        }

        $tenant->markEmailAsVerified();

        return [
            'tenant' => $tenant->fresh(),
        ];
    }
}
