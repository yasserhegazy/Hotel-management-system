<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Staff\Domain\DTOs\CreateStaffDTO;
use Modules\Staff\Domain\Services\StaffManagementService;
use Modules\Staff\Http\Requests\CreateStaffRequest;
use Modules\Staff\Http\Resources\StaffUserResource;

class CreateStaffController extends Controller
{
    public function __construct(
        private readonly StaffManagementService $service,
    ) {}

    public function __invoke(CreateStaffRequest $request): JsonResponse
    {
        $dto = CreateStaffDTO::fromArray($request->validated());
        $user = $this->service->create($dto);

        return response()->json([
            'message' => 'Staff member created successfully. Setup email sent.',
            'data' => new StaffUserResource($user),
        ], 201);
    }
}
