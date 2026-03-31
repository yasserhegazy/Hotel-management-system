<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Exceptions;

use Exception;

class StaffAlreadyActivatedException extends Exception
{
    public function __construct(string $message = 'Staff member is already activated.')
    {
        parent::__construct($message);
    }
}
