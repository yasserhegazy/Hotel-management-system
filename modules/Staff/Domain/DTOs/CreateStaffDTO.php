<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\DTOs;

readonly class CreateStaffDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public ?string $phone,
        public string $preferredLanguage,
        public array $roles,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            preferredLanguage: $data['preferred_language'] ?? 'en',
            roles: $data['roles'],
        );
    }
}
