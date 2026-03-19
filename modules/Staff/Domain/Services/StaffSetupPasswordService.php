<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\Staff\Domain\DTOs\SetupPasswordDTO;
use Modules\Staff\Domain\Exceptions\InvalidSetupTokenException;
use Modules\Staff\Domain\Models\TenantUser;

class StaffSetupPasswordService
{
    /**
     * Handle password setup and account activation.
     *
     * @throws InvalidSetupTokenException
     */
    public function handle(SetupPasswordDTO $dto): TenantUser
    {
        $user = TenantUser::where('email', $dto->email)->first();

        // Check if already activated (return generic error for security)
        if ($user && $user->isActivated()) {
            Log::warning('Setup password failed: account already activated', [
                'email' => $dto->email,
                'ip' => request()->ip(),
            ]);
            throw new InvalidSetupTokenException;
        }

        $tokenIsValid = $this->isValidToken($user, $dto->token);

        if (! $tokenIsValid) {
            Log::warning('Setup password failed: invalid or expired token', [
                'email' => $dto->email,
                'user_exists' => $user !== null,
                'ip' => request()->ip(),
            ]);
            throw new InvalidSetupTokenException;
        }

        // Token is valid - proceed with atomic activation to prevent race conditions
        // Use atomic update with WHERE clause matching the token to ensure single-use
        $hashedToken = hash('sha256', $dto->token);
        $affected = DB::table('tenant_users')
            ->where('id', $user->id)
            ->where('setup_token', $hashedToken)
            ->whereNotNull('setup_token')
            ->where('setup_token_expires_at', '>', now())
            ->update([
                'password' => Hash::make($dto->password),
                'is_active' => true,
                'activated_at' => now(),
                'setup_token' => null,
                'setup_token_expires_at' => null,
                'updated_at' => now(),
            ]);

        // If no rows affected, token was consumed by a concurrent request
        if ($affected === 0) {
            Log::warning('Setup password failed: token already consumed (race condition)', [
                'email' => $dto->email,
                'user_id' => $user->id,
                'ip' => request()->ip(),
            ]);
            throw new InvalidSetupTokenException;
        }

        Log::info('Staff account activated successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip(),
        ]);

        return $user->fresh();
    }

    /**
     * Validate the setup token against the stored hash.
     */
    private function isValidToken(?TenantUser $user, string $plainToken): bool
    {
        if (! $user) {
            return false;
        }

        // Check if token exists
        if ($user->setup_token === null) {
            return false;
        }

        // Check if token has expired
        if ($user->setup_token_expires_at === null || $user->setup_token_expires_at->isPast()) {
            return false;
        }

        // Verify token hash matches
        $hashedToken = hash('sha256', $plainToken);

        return hash_equals($user->setup_token, $hashedToken);
    }
}
