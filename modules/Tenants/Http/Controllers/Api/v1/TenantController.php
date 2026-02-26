<?php

declare(strict_types=1);

namespace Modules\Tenants\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Tenants\Domain\Models\Tenant;
use Modules\Tenants\Domain\Services\UpdateTenantService;
use Modules\Tenants\Http\Requests\UpdateTenantRequest;
use Modules\Tenants\Http\Resources\TenantResource;

class TenantController extends Controller
{
    public function __construct(
        private UpdateTenantService $updateTenantService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $tenant = Tenant::with('location')
            ->findOrFail($request->user()->tenant_id);

        return response()->json(new TenantResource($tenant));
    }

    public function update(UpdateTenantRequest $request): JsonResponse
    {
        $validated = $request->validated();
        unset($validated['profile_image'], $validated['remove_profile_image']);

        $updatedTenant = $this->updateTenantService->handle(
            tenant: $request->tenant(),
            data: $validated,
            profileImage: $request->file('profile_image'),
            removeProfileImage: $request->boolean('remove_profile_image'),
        );

        return response()->json([
            'message' => 'Tenant updated successfully.',
            'tenant' => new TenantResource($updatedTenant),
        ]);
    }
}
