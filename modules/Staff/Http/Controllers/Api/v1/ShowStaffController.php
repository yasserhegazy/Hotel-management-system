<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Staff\Domain\Services\StaffManagementService;
use Modules\Staff\Http\Resources\StaffUserResource;

class ShowStaffController extends Controller
{
    public function __construct(
        private readonly StaffManagementService $service,
    ) {}

    public function __invoke(int $staff_id): JsonResponse
    {
        $user = $this->service->show($staff_id);

        return response()->json([
            'data' => new StaffUserResource($user),
        ]);
    }
}
