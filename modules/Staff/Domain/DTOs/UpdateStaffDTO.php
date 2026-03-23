<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\DTOs;

readonly class UpdateStaffDTO
{
    public function __construct(
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?string $phone,
        public ?string $preferredLanguage,
        public ?array $roles,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            preferredLanguage: $data['preferred_language'] ?? null,
            roles: $data['roles'] ?? null,
        );
    }
}
