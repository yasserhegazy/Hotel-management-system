<?php

namespace Modules\Auth\Domain\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegisterUserService
{
    public function handle(array $data): User
    {
        $user = User::create($data);

        Auth::login($user);

        return $user;
    }
}
