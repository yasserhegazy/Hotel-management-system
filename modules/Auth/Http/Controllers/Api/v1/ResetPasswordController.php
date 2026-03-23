<?php

namespace Modules\Auth\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Modules\Auth\Domain\Services\ResetPasswordService;
use Modules\Auth\Http\Requests\ResetPasswordRequest;

class ResetPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request, ResetPasswordService $service): JsonResponse
    {
        try {
            $status = $service->resetPassword($request->validated());

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Password has been reset successfully.',
                    'status' => $status,
                ], 200);
            }

            if ($status === Password::INVALID_USER) {
                return response()->json(['error' => __($status)], 400);
            }

            if ($status === Password::INVALID_TOKEN) {
                return response()->json(['status' => $status], 422);
            }

            return response()->json(['error' => __($status)], 400);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Server Error'], 500);
        }
    }
}
