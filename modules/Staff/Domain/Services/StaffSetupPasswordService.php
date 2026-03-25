<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Services;

use Illuminate\Support\Facades\DB;
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
            throw new InvalidSetupTokenException;
        }

        $tokenIsValid = $this->isValidToken($user, $dto->token);

        if (! $tokenIsValid) {
            throw new InvalidSetupTokenException;
        }

        // Token is valid - proceed with atomic activation to prevent race conditions.
        $hashedToken = hash('sha256', $dto->token);
        $currentTime = now();

        $activated = DB::transaction(function () use ($user, $dto, $hashedToken, $currentTime): bool {
            $lockedUser = TenantUser::query()
                ->lockForUpdate()
                ->where('id', $user->id)
                ->where('setup_token', $hashedToken)
                ->where('setup_token_expires_at', '>', $currentTime)
                ->first();

            if (! $lockedUser) {
                return false;
            }

            $lockedUser->password = $dto->password;
            $lockedUser->is_active = true;
            $lockedUser->activated_at = $currentTime;
            $lockedUser->setup_token = null;
            $lockedUser->setup_token_expires_at = null;
            $lockedUser->save();

            return true;
        });

        // If lock found no matching row, token was consumed by a concurrent request
        if (! $activated) {
            Log::warning('Setup password failed: token already consumed (race condition)', [
                'user_id' => $user->id,
            ]);
            throw new InvalidSetupTokenException;
        }

        Log::info('Staff account activated successfully', [
            'user_id' => $user->id,
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
