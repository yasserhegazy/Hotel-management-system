<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Staff\Domain\Services\StaffManagementService;
use Modules\Staff\Http\Resources\StaffUserResource;

class ListStaffController extends Controller
{
    public function __construct(
        private readonly StaffManagementService $service,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $paginator = $this->service->list($request->query());

        return response()->json([
            'data' => StaffUserResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}
