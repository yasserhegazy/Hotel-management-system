<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Staff\Domain\DTOs\CreateStaffDTO;
use Modules\Staff\Domain\DTOs\UpdateStaffDTO;
use Modules\Staff\Domain\Exceptions\SelfDeactivationException;
use Modules\Staff\Domain\Exceptions\StaffAlreadyActivatedException;
use Modules\Staff\Domain\Mail\StaffSetupMail;
use Modules\Staff\Domain\Models\TenantUser;
use Modules\Staff\Domain\Repositories\TenantUserRepository;

class StaffManagementService
{
    public function __construct(
        private readonly TenantUserRepository $repository,
    ) {}

    public function list(array $filters): LengthAwarePaginator
    {
        return $this->repository->listFiltered($filters);
    }

    public function show(int $staffId): TenantUser
    {
        return $this->repository->findOrFail($staffId);
    }

    public function create(CreateStaffDTO $dto): TenantUser
    {
        $plainToken = Str::random(32);
        $tenantKey = (string) (tenancy()->tenant?->getKey() ?? '');

        $user = DB::transaction(function () use ($dto, $plainToken) {
            $user = $this->repository->create([
                'first_name' => $dto->firstName,
                'last_name' => $dto->lastName,
                'email' => $dto->email,
                'phone' => $dto->phone,
                'preferred_language' => $dto->preferredLanguage,
                'password' => null,
                'is_active' => false,
                'activated_at' => null,
                'setup_token' => hash('sha256', $plainToken),
                'setup_token_expires_at' => now()->addHours(48),
            ]);

            $user->syncRoles($dto->roles);

            return $user->load('roles');
        });

        Mail::queue(new StaffSetupMail($user, $plainToken, $tenantKey));

        Log::info('Staff member created', [
            'user_id' => $user->id,
            'email' => $user->email,
            'roles' => $dto->roles,
        ]);

        return $user;
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $staffId, UpdateStaffDTO $dto): TenantUser
    {
        return DB::transaction(function () use ($staffId, $dto) {
            $user = $this->repository->findOrFail($staffId);

            $fields = array_filter([
                'first_name' => $dto->firstName,
                'last_name' => $dto->lastName,
                'email' => $dto->email,
                'phone' => $dto->phone,
                'preferred_language' => $dto->preferredLanguage,
            ], fn ($value) => $value !== null);

            if ($dto->roles !== null) {
                $user->syncRoles($dto->roles);
            }

            if (! empty($fields)) {
                $user = $this->repository->update($user, $fields);
            } else {
                $user = $user->fresh()->load('roles');
            }

            Log::info('Staff member updated', [
                'user_id' => $staffId,
                'fields' => array_keys($fields),
                'roles_changed' => $dto->roles !== null,
            ]);

            return $user;
        });
    }

    /**
     * @throws SelfDeactivationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deactivate(int $staffId, int $actorId): TenantUser
    {
        $user = $this->repository->findOrFail($staffId);

        if ($user->id === $actorId) {
            throw new SelfDeactivationException;
        }

        $this->repository->update($user, ['is_active' => false]);

        Log::info('Staff member deactivated', [
            'user_id' => $user->id,
            'email' => $user->email,
            'deactivated_by' => $actorId,
        ]);

        return $user;
    }

    /**
     * @throws StaffAlreadyActivatedException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function resendSetup(int $staffId): TenantUser
    {
        $user = $this->repository->findOrFail($staffId);

        if ($user->isActivated()) {
            throw new StaffAlreadyActivatedException;
        }

        $plainToken = Str::random(32);

        $this->repository->update($user, [
            'setup_token' => hash('sha256', $plainToken),
            'setup_token_expires_at' => now()->addHours(48),
        ]);

        Mail::queue(new StaffSetupMail($user, $plainToken, (string) (tenancy()->tenant?->getKey() ?? '')));

        Log::info('Staff setup email resent', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return $user;
    }
}
