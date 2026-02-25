<?php

declare(strict_types=1);

namespace Modules\Tenants\Domain\Enums;

enum TenantStatus: string
{
    case PendingVerification = 'pending_verification';
    case Verified = 'verified';
    case Active = 'active';
    case Disabled = 'disabled';
}
