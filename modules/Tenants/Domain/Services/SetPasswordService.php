<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Modules\Tenants\Domain\DTOs\SetPasswordDTO;
use Modules\Tenants\Domain\Enums\TenantStatus;
use Modules\Tenants\Domain\Exceptions\TenantNotVerifiedException;
use Modules\Tenants\Domain\Models\Tenant;
use Modules\Tenants\Domain\Repositories\TenantRepository;
use Modules\Tenants\Mail\TenantWelcomeMail;

class SetPasswordService
{
    public function __construct(
        protected TenantDatabaseService $tenantDatabaseService,
        private TenantRepository $tenantRepository,
    ) {}

    /**
     * Handle owner creation and tenant activation.
     *
     * @throws TenantNotVerifiedException
     * @throws ValidationException
     */
    public function handle(SetPasswordDTO $dto): array
    {
        $tenant = $this->tenantRepository->findByVerificationToken(
            Tenant::hashToken($dto->token)
        );

        if (! $tenant) {
            throw new TenantNotVerifiedException('Invalid registration token.');
        }

        if (! $tenant->verification_expires_at || $tenant->verification_expires_at->isPast()) {
            throw new TenantNotVerifiedException('Registration token has expired.');
        }

        if (! $tenant->isVerified()) {
            throw new TenantNotVerifiedException('Tenant email must be verified before proceeding.');
        }

        if ($tenant->owner_id !== null) {
            throw ValidationException::withMessages(['token' => 'An owner account already exists for this tenant.']);
        }

        $this->tenantDatabaseService->create($tenant);

        $result = DB::transaction(function () use ($tenant, $dto) {
            if (User::where('email', $tenant->email)->exists()) {
                throw ValidationException::withMessages(['token' => 'An owner account already exists for this tenant.']);
            }

            $user = User::create([
                'first_name' => $dto->firstName,
                'last_name' => $dto->lastName,
                'email' => $tenant->email,
                'password' => $dto->password,
            ]);

            $updatedTenant = $this->tenantRepository->update($tenant, [
                'owner_id' => $user->id,
                'status' => TenantStatus::Active,
                'verification_token' => null,
                'verification_expires_at' => null,
            ]);

            return [
                'tenant' => $updatedTenant,
                'user' => $user,
            ];
        });

        Auth::login($result['user']);

        Mail::to($tenant->email)->queue(
            new TenantWelcomeMail($result['tenant'], trim($dto->firstName.' '.$dto->lastName))
        );

        return $result;
    }
}
