<?php

namespace Modules\Auth\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
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
        $user = $this->registerUserService->handle($request->validated());

        return response()->json(
            (new UserResource($user))->resolve(),
            201
        );
    }
}
