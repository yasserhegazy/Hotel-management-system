<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            return null;
        }

        // Check if account is active
        if (! $user->is_active) {
            return null;
        }

        // Check if password has been set (not pending setup)
        if ($user->password === null) {
            return null;
        }

        // Verify password
        if (! Hash::check($dto->password, $user->password)) {
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
