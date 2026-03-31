<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Modules\Staff\Database\Factories\TenantUserFactory;
use Spatie\Permission\Traits\HasRoles;

class TenantUser extends Authenticatable
{
    /** @use HasFactory<TenantUserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $table = 'tenant_users';

    protected $guard_name = 'tenant';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'preferred_language',
        'is_active',
        'activated_at',
        'setup_token',
        'setup_token_expires_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'setup_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'activated_at' => 'datetime',
            'setup_token_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function setPasswordAttribute(?string $value): void
    {
        $this->attributes['password'] = $value !== null ? Hash::make($value) : null;
    }

    protected static function newFactory(): TenantUserFactory
    {
        return TenantUserFactory::new();
    }

    public function isActivated(): bool
    {
        return $this->is_active && $this->activated_at !== null;
    }

    public function hasValidSetupToken(): bool
    {
        return $this->setup_token !== null
            && $this->setup_token_expires_at !== null
            && $this->setup_token_expires_at->isFuture();
    }

    public function scopeSearch($query, string $term): void
    {
        $query->where(function ($q) use ($term) {
            $q->where('first_name', 'LIKE', "%{$term}%")
                ->orWhere('last_name', 'LIKE', "%{$term}%")
                ->orWhere('email', 'LIKE', "%{$term}%")
                ->orWhere('phone', 'LIKE', "%{$term}%");
        });
    }

    public function scopeActive($query, bool $isActive): void
    {
        $query->where('is_active', $isActive);
    }
}
