<?php

namespace Modules\Auth\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Domain\Services\RegisterUserService;
use Modules\Auth\Http\Requests\UserRegisterRequest;
use Modules\Auth\Http\Resources\UserResource;

class RegisterController extends Controller
{
    public function __construct(
        private RegisterUserService $registerUserService
    ) {}

    public function __invoke(UserRegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $this->registerUserService
                ->handle($request->validated());

            DB::commit();

            // Auto login (Sanctum session-based)
            Auth::login($user);

            return (new UserResource($user))
                ->response()
                ->setStatusCode(201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Registration failed.',
            ], 500);
        }
    }
}
