<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Tenants\Database\Factories\TenantFactory;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasFactory;

    // Status constants
    const STATUS_PENDING_VERIFICATION = 'pending_verification';

    const STATUS_VERIFIED = 'verified';

    const STATUS_ACTIVE = 'active';

    const STATUS_DISABLED = 'disabled';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'description',
        'profile_image_path',
        'slug',
        'database_name',
        'owner_id',
        'subscription_id',
        'location_id',
        'status',
        'email_verified_at',
        'verification_token',
        'verification_expires_at',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'description',
            'profile_image_path',
            'slug',
            'database_name',
            'owner_id',
            'subscription_id',
            'location_id',
            'status',
            'email_verified_at',
            'verification_token',
            'verification_expires_at',
            'created_at',
            'updated_at',
        ];
    }

    protected $casts = [
        'owner_id' => 'integer',
        'subscription_id' => 'integer',
        'location_id' => 'integer',
        'status' => 'string',
        'email_verified_at' => 'datetime',
        'verification_expires_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class, 'tenant_id', 'id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    // Status check methods
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDisabled(): bool
    {
        return $this->status === self::STATUS_DISABLED;
    }

    public function isPendingVerification(): bool
    {
        return $this->status === self::STATUS_PENDING_VERIFICATION;
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    // Email verification methods
    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'status' => self::STATUS_VERIFIED,
        ])->save();
    }

    // Verification token helpers
    public static function generatePlainToken(): string
    {
        return bin2hex(random_bytes(32)); // 64-char hex string
    }

    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    protected static function newFactory(): TenantFactory
    {
        return new TenantFactory;
    }
}
