<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Exceptions;

use Exception;

class InvalidSetupTokenException extends Exception
{
    public function __construct(string $message = 'Invalid or expired setup token.')
    {
        parent::__construct($message);
    }
}
