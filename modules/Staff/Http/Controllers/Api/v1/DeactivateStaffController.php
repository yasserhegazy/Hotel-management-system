<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Staff\Domain\Exceptions\SelfDeactivationException;
use Modules\Staff\Domain\Services\StaffManagementService;

class DeactivateStaffController extends Controller
{
    public function __construct(
        private readonly StaffManagementService $service,
    ) {}

    public function __invoke(int $staff_id): JsonResponse
    {
        try {
            $this->service->deactivate($staff_id, Auth::guard('tenant')->id());

            return response()->json([
                'message' => 'Staff member deactivated successfully.',
            ]);
        } catch (SelfDeactivationException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 403);
        }
    }
}
