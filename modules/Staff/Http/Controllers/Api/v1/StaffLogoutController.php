<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Staff\Domain\Services\StaffAuthService;

class StaffLogoutController extends Controller
{
    public function __construct(
        private readonly StaffAuthService $authService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $this->authService->logout($request);

        return response()->noContent();
    }
}
