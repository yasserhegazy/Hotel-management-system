<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Services;

use Illuminate\Support\Facades\Log;
use Modules\Staff\Domain\DTOs\SetupPasswordDTO;
use Modules\Staff\Domain\Exceptions\AlreadyActivatedException;
use Modules\Staff\Domain\Exceptions\InvalidSetupTokenException;
use Modules\Staff\Domain\Models\TenantUser;

class StaffSetupPasswordService
{
    /**
     * Handle password setup and account activation.
     *
     * @throws AlreadyActivatedException
     * @throws InvalidSetupTokenException
     */
    public function handle(SetupPasswordDTO $dto): TenantUser
    {
        $user = TenantUser::where('email', $dto->email)->first();

        $tokenIsValid = $this->isValidToken($user, $dto->token);

        // If token is invalid, determine the appropriate error
        if (! $tokenIsValid) {
            if ($user && $user->isActivated()) {
                // Distinguish: token was consumed vs never had a token
                // Empty string '' = token was consumed (setup completed)
                // Null = never had a token (created already active)
                if ($user->setup_token === '') {
                    Log::warning('Setup password failed: token already consumed', [
                        'email' => $dto->email,
                        'ip' => request()->ip(),
                    ]);
                    throw new InvalidSetupTokenException;  // Token consumed (400)
                }

                Log::warning('Setup password failed: account already activated', [
                    'email' => $dto->email,
                    'ip' => request()->ip(),
                ]);
                throw new AlreadyActivatedException;  // Never had token (409)
            }

            Log::warning('Setup password failed: invalid or expired token', [
                'email' => $dto->email,
                'user_exists' => $user !== null,
                'ip' => request()->ip(),
            ]);
            throw new InvalidSetupTokenException;
        }

        // Token is valid - proceed with activation
        // Set setup_token to empty string as marker that token was consumed
        $user->update([
            'password' => $dto->password,
            'is_active' => true,
            'activated_at' => now(),
            'setup_token' => '',  // Empty string marks "token was consumed"
            'setup_token_expires_at' => null,
        ]);

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
