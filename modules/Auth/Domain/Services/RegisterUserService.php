<?php

namespace Modules\Auth\Domain\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUserService
{
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'preferred_language' => $data['preferred_language'] ?? 'en',
            ]);

            Auth::login($user);

            return $user;
        });
    }
}
