<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\DTOs;

final class LocationDTO
{
    public function __construct(
        public readonly string $countryCode,
        public readonly string $city,
        public readonly ?string $regionName = null,
        public readonly ?string $addressLine = null,
        public readonly ?string $postalCode = null,
        public readonly ?string $timezone = null,
    ) {}

    /**
     * Create DTO from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            countryCode: $data['country_code'],
            city: $data['city'],
            regionName: $data['region_name'] ?? null,
            addressLine: $data['address_line'] ?? null,
            postalCode: $data['postal_code'] ?? null,
            timezone: $data['timezone'] ?? null,
        );
    }

    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return [
            'country_code' => $this->countryCode,
            'city' => $this->city,
            'region_name' => $this->regionName,
            'address_line' => $this->addressLine,
            'postal_code' => $this->postalCode,
            'timezone' => $this->timezone,
        ];
    }
}
