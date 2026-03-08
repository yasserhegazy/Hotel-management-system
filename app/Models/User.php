<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

/**
 * @method static create(array $data)
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'preferred_language',
        'is_guest',
        'last_login_at',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Override the default password reset notification to use Laravel's built-in template with custom email body.
     */
    public function sendPasswordResetNotification($token): void
    {
        try {
            $this->notify(new ResetPassword($token));
        } catch (\Throwable $e) {
            // Log the exception so we can debug notification failures
            Log::error('Failed to send password reset notification', [
                'user_id' => $this->id ?? null,
                'email' => $this->email ?? null,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            // Optionally rethrow if you want the caller to handle failures
            // throw $e;
        }
    }
}
