<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\DTOs;

final class InitRegistrationDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly LocationDTO $location,
    ) {}

    /**
     * Create DTO from array (typically from request data)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'],
            location: LocationDTO::fromArray($data['location']),
        );
    }

    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'location' => $this->location->toArray(),
        ];
    }
}
