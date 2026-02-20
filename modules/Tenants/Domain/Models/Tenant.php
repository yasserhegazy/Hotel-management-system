<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Tenants\Database\Factories\TenantFactory;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'slug',
        'database_name',
        'owner_id',
        'subscription_id',
        'location_id',
        'status',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'slug',
            'database_name',
            'owner_id',
            'subscription_id',
            'location_id',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    protected $casts = [
        'owner_id' => 'integer',
        'subscription_id' => 'integer',
        'location_id' => 'integer',
        'status' => 'string',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class, 'tenant_id', 'id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDisabled(): bool
    {
        return $this->status === 'disabled';
    }

    protected static function newFactory(): TenantFactory
    {
        return new TenantFactory;
    }
}
