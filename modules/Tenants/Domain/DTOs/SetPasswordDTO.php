<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\DTOs;

final class SetPasswordDTO
{
    public function __construct(
        public readonly string $token,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $password,
    ) {}

    /**
     * Create DTO from array (typically from request data)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            password: $data['password'],
        );
    }

    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'password' => $this->password,
        ];
    }
}
