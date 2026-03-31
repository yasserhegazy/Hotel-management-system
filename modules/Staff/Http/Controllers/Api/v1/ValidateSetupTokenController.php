<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Staff\Domain\Models\TenantUser;
use Modules\Staff\Http\Requests\ValidateSetupTokenRequest;

class ValidateSetupTokenController extends Controller
{
    public function __invoke(ValidateSetupTokenRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = TenantUser::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json(['status' => 'invalid'], 422);
        }

        if ($user->isActivated()) {
            return response()->json(['status' => 'already_activated'], 200);
        }

        if ($user->setup_token === null
            || $user->setup_token_expires_at?->isPast()
            || ! hash_equals($user->setup_token, hash('sha256', $validated['token']))
        ) {
            return response()->json(['status' => 'invalid'], 422);
        }

        return response()->json(['status' => 'valid'], 200);
    }
}
