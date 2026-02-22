<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\Exceptions;

use Exception;

class TenantAlreadyVerifiedException extends Exception
{
    public function __construct(string $message = 'Tenant email is already verified.', int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
