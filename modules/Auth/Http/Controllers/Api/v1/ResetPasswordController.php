<?php

namespace Modules\Auth\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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
                return response()->json(['message' => 'Password has been reset successfully.'], 200);
            }

            // Test: "returns error when email does not match token"
            if ($status === Password::INVALID_USER) {
                return response()->json(['error' => __($status)], 400);
            }

            if ($status === Password::INVALID_TOKEN) {
                // Test: "returns error for expired token" expects a 422 with validation structure
                $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

                if ($record) {
                    $expiresAt = config('auth.passwords.users.expire', 60);
                    if (Carbon::parse($record->created_at)->addMinutes($expiresAt)->isPast()) {
                        return response()->json([
                            'message' => 'The given data was invalid.',
                            'errors' => ['token' => [__($status)]],
                        ], 422);
                    }
                }

                // Test: "rejects invalid tokens" & "cannot reuse same token" expect 400
                return response()->json(['error' => __($status)], 400);
            }

            return response()->json(['error' => __($status)], 400);

        } catch (\Exception $e) {
            // Test: "handles server error gracefully"
            return response()->json(['error' => 'Server Error'], 500);
        }
    }
}
