<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Exceptions;

use Exception;

class SelfDeactivationException extends Exception
{
    public function __construct(string $message = 'You cannot deactivate your own account.')
    {
        parent::__construct($message);
    }
}
