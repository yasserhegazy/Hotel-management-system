<?php

namespace Modules\Auth\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Modules\Auth\Domain\Services\ResetPasswordService;
use Modules\Auth\Http\Requests\ForgotPasswordRequest;

class ForgotPasswordController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request, ResetPasswordService $service): JsonResponse
    {
        try {
            $service->sendResetLink($request->validated());

            // Return both the 'message' (for old tests) and 'status' (for the new test)
            return response()->json([
                'message' => 'Password reset link sent if the email exists.',
                'status' => Password::RESET_LINK_SENT,
            ], 200);

        } catch (\Throwable $e) {
            // Log the exception for debugging/observability
            Log::error('Failed to send password reset link', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return response()->json(['error' => 'Server Error'], 500);
        }
    }
}
