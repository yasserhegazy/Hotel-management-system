<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Tenants\Database\Factories\LocationFactory;
use PragmaRX\Countries\Package\Countries;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_code',
        'country_name',
        'region_name',
        'city',
        'address_line',
        'postal_code',
        'timezone',
    ];

    /**
     * Create location with country name and timezone derived from country code
     */
    public static function createFromCode(array $attributes): self
    {
        $attributes['country_code'] = strtoupper((string) $attributes['country_code']);

        $countries = new Countries;
        $country = $countries->where('cca2', $attributes['country_code'])->first();

        // Set country name from ISO code
        $attributes['country_name'] = $country?->name?->common ?? $attributes['country_code'];

        // Auto-detect timezone if not provided
        if (empty($attributes['timezone']) && $country) {
            $timezones = $country->timezones ?? null;
            $attributes['timezone'] = $timezones ? collect($timezones)->first() : null;
        }

        return self::create($attributes);
    }

    /**
     * Get the tenants at this location
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * Get formatted full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line,
            $this->city,
            $this->region_name,
            $this->country_name,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }

    protected static function newFactory(): LocationFactory
    {
        return new LocationFactory;
    }
}
