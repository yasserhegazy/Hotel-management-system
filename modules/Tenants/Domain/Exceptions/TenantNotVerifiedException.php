<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\Exceptions;

use Exception;

class TenantNotVerifiedException extends Exception
{
    public function __construct(string $message = 'Tenant email must be verified before proceeding.', int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
