<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\DTOs;

readonly class SetupPasswordDTO
{
    public function __construct(
        public string $token,
        public string $email,
        public string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'],
            email: $data['email'],
            password: $data['password'],
        );
    }
}
