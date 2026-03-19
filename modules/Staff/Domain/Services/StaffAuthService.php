<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\Staff\Domain\DTOs\StaffLoginDTO;
use Modules\Staff\Domain\Models\TenantUser;

class StaffAuthService
{
    /**
     * Attempt to authenticate staff with given credentials.
     *
     * @return TenantUser|null Returns the authenticated user or null on failure
     */
    public function login(StaffLoginDTO $dto, Request $request): ?TenantUser
    {
        $user = TenantUser::where('email', $dto->email)->first();

        if (! $user) {
            Log::warning('Staff login failed: user not found', [
                'email' => $dto->email,
                'ip' => request()->ip(),
            ]);

            return null;
        }

        // Check if account is active
        if (! $user->is_active) {
            Log::warning('Staff login failed: account inactive', [
                'email' => $dto->email,
                'user_id' => $user->id,
                'ip' => request()->ip(),
            ]);

            return null;
        }

        // Check if password has been set (not pending setup)
        if ($user->password === null) {
            Log::warning('Staff login failed: password not set (pending activation)', [
                'email' => $dto->email,
                'user_id' => $user->id,
                'ip' => request()->ip(),
            ]);

            return null;
        }

        // Verify password
        if (! Hash::check($dto->password, $user->password)) {
            Log::warning('Staff login failed: invalid password', [
                'email' => $dto->email,
                'user_id' => $user->id,
                'ip' => request()->ip(),
            ]);

            return null;
        }

        // Update last_login_at
        $user->update(['last_login_at' => now()]);

        // Login the user with tenant guard
        Auth::guard('tenant')->login($user);

        // Regenerate session to prevent session fixation attacks
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        Log::info('Staff login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip(),
        ]);

        return $user->fresh();
    }

    /**
     * Logout the currently authenticated staff member.
     */
    public function logout(Request $request): void
    {
        Auth::guard('tenant')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        Auth::forgetGuards();
    }
}
