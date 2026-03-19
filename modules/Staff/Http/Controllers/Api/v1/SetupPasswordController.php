<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Staff\Domain\DTOs\SetupPasswordDTO;
use Modules\Staff\Domain\Exceptions\AlreadyActivatedException;
use Modules\Staff\Domain\Exceptions\InvalidSetupTokenException;
use Modules\Staff\Domain\Services\StaffSetupPasswordService;
use Modules\Staff\Http\Requests\SetupPasswordRequest;

class SetupPasswordController extends Controller
{
    public function __construct(
        private readonly StaffSetupPasswordService $setupPasswordService,
    ) {}

    public function __invoke(SetupPasswordRequest $request): JsonResponse
    {
        $dto = SetupPasswordDTO::fromArray($request->validated());

        try {
            $this->setupPasswordService->handle($dto);

            return response()->json([
                'message' => 'Password set successfully. Your account is now active.',
            ]);
        } catch (AlreadyActivatedException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 409);
        } catch (InvalidSetupTokenException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
