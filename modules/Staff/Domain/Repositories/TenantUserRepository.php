<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Staff\Domain\Models\TenantUser;

class TenantUserRepository
{
    public function findOrFail(int $id): TenantUser
    {
        return TenantUser::with('roles')->findOrFail($id);
    }

    public function create(array $data): TenantUser
    {
        return TenantUser::create($data);
    }

    public function update(TenantUser $user, array $data): TenantUser
    {
        $user->update($data);

        return $user->fresh()->load('roles');
    }

    public function listFiltered(array $filters): LengthAwarePaginator
    {
        $query = TenantUser::with('roles');

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['is_active'])) {
            $query->active((bool) $filters['is_active']);
        }

        if (isset($filters['role'])) {
            $query->role($filters['role']);
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->paginate($perPage);
    }
}
