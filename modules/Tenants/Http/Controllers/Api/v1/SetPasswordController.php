<?php

declare(strict_types=1);

namespace Modules\Tenants\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Http\Resources\UserResource;
use Modules\Tenants\Domain\DTOs\SetPasswordDTO;
use Modules\Tenants\Domain\Exceptions\TenantNotVerifiedException;
use Modules\Tenants\Domain\Services\SetPasswordService;
use Modules\Tenants\Http\Requests\SetPasswordRequest;
use Modules\Tenants\Http\Resources\TenantResource;

class SetPasswordController extends Controller
{
    public function __construct(
        private SetPasswordService $setPasswordService
    ) {}

    public function __invoke(SetPasswordRequest $request): JsonResponse
    {
        try {
            $dto = SetPasswordDTO::fromArray($request->validated());

            $result = $this->setPasswordService->handle($dto);

            return response()->json([
                'message' => 'Owner account created and tenant activated.',
                'tenant' => new TenantResource($result['tenant']),
                'user' => new UserResource($result['user']),
            ], 201);
        } catch (TenantNotVerifiedException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
        // ValidationException propagates to Laravel's handler → 422 with errors format
    }
}
