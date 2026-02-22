<?php

declare(strict_types=1);

namespace Modules\Tenants\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Tenants\Domain\DTOs\InitRegistrationDTO;
use Modules\Tenants\Domain\Services\InitRegistrationService;
use Modules\Tenants\Http\Requests\InitRegistrationRequest;

class InitRegistrationController extends Controller
{
    public function __construct(
        private InitRegistrationService $initRegistrationService
    ) {}

    public function __invoke(InitRegistrationRequest $request): JsonResponse
    {
        $dto = InitRegistrationDTO::fromArray($request->validated());

        $result = $this->initRegistrationService->handle($dto);

        return response()->json([
            'message' => 'Verification email sent to hotel email.',
            'tenant_id' => $result['tenant']->id,
        ], 201);
    }
}
