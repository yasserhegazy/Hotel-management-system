<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Staff\Domain\DTOs\UpdateStaffDTO;
use Modules\Staff\Domain\Services\StaffManagementService;
use Modules\Staff\Http\Requests\UpdateStaffRequest;
use Modules\Staff\Http\Resources\StaffUserResource;

class UpdateStaffController extends Controller
{
    public function __construct(
        private readonly StaffManagementService $service,
    ) {}

    public function __invoke(UpdateStaffRequest $request, int $staff_id): JsonResponse
    {
        $dto = UpdateStaffDTO::fromArray($request->validated());
        $user = $this->service->update($staff_id, $dto);

        return response()->json([
            'message' => 'Staff member updated successfully.',
            'data' => new StaffUserResource($user),
        ]);
    }
}
