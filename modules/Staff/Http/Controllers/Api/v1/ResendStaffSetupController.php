<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Staff\Domain\Exceptions\StaffAlreadyActivatedException;
use Modules\Staff\Domain\Services\StaffManagementService;

class ResendStaffSetupController extends Controller
{
    public function __construct(
        private readonly StaffManagementService $service,
    ) {}

    public function __invoke(int $staff_id): JsonResponse
    {
        try {
            $this->service->resendSetup($staff_id);

            return response()->json([
                'message' => 'Setup email resent successfully.',
            ]);
        } catch (StaffAlreadyActivatedException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 409);
        }
    }
}
