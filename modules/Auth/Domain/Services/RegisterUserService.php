<?php

namespace Modules\Auth\Domain\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUserService
{
    public function handel(array $data): User
    {
        return User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'preferred_language' => 'en',
        ]);
    }
}
