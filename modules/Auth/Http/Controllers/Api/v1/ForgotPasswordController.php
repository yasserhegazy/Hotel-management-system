<?php

namespace Modules\Auth\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Domain\Services\ResetPasswordService;
use Modules\Auth\Http\Requests\ForgotPasswordRequest;

class ForgotPasswordController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request, ResetPasswordService $service): JsonResponse
    {
        try {

            $service->sendResetLink($request->validated());

            return response()->json([
                'message' => 'Password reset link sent if the email exists.',
            ], 200);

        } catch (\Exception $e) {

            return response()->json(['error' => 'Server Error'], 500);
        }
    }
}
