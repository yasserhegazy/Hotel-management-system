<?php

namespace Modules\Auth\Domain\Services;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordService
{
    public function sendResetLink(array $credentials): string
    {
        return Password::broker()->sendResetLink($credentials);
    }

    public function resetPassword(array $data): string
    {
        return Password::broker()->reset(
            $data,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );
    }
}
