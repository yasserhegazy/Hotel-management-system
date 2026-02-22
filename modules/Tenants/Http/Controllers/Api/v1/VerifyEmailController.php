<?php

declare(strict_types=1);

namespace Modules\Tenants\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Modules\Tenants\Domain\Exceptions\TenantAlreadyVerifiedException;
use Modules\Tenants\Domain\Services\VerifyEmailService;

class VerifyEmailController extends Controller
{
    public function __construct(
        private VerifyEmailService $verifyEmailService
    ) {}

    public function __invoke(string $token): JsonResponse
    {
        if (! preg_match('/^[a-f0-9]{64}$/', $token)) {
            return response()->json(['error' => 'Invalid or expired token.'], 400);
        }

        try {
            $result = $this->verifyEmailService->handle($token);

            return response()->json([
                'message' => 'Token verified. Proceed to set password.',
                'tenant_id' => $result['tenant']->id,
            ], 200);
        } catch (TenantAlreadyVerifiedException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Token not found.'], 404);
        }
    }
}
