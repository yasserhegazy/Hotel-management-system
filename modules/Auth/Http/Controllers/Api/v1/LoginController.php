<?php

namespace Modules\Auth\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\Http\Requests\UserLoginRequest;
use Modules\Auth\Http\Resources\UserResource;

class LoginController extends Controller
{
    public function __invoke(UserLoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->validated())) {
            return response()->json(['error' => 'Invalid credentials.'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => (new UserResource($user))->resolve(),
            'token' => $token,
        ]);
    }
}
