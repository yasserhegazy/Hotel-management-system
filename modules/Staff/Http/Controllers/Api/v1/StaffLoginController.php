<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Staff\Domain\DTOs\StaffLoginDTO;
use Modules\Staff\Domain\Services\StaffAuthService;
use Modules\Staff\Http\Requests\StaffLoginRequest;
use Modules\Staff\Http\Resources\StaffUserResource;

class StaffLoginController extends Controller
{
    public function __construct(
        private readonly StaffAuthService $authService,
    ) {}

    public function __invoke(StaffLoginRequest $request): JsonResponse
    {
        $dto = StaffLoginDTO::fromArray($request->validated());

        $user = $this->authService->login($dto, $request);

        if (! $user) {
            return response()->json([
                'error' => 'Invalid credentials or inactive account.',
            ], 401);
        }

        return response()->json([
            'message' => 'Logged in successfully.',
            'user' => new StaffUserResource($user),
            'tenant_id' => tenancy()->tenant?->getKey(),
        ]);
    }
}
