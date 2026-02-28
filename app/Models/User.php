<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
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

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Hash::make($value),
        );
    }

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
     * Override the default password reset notification to use a custom template.
     */
    public function sendPasswordResetNotification($token): void
    {
        try {
            $this->notify(new ResetPasswordNotification($token));
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
