<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Enums;

enum StaffRole: string
{
    case HotelAdmin = 'hotel_admin';
    case Manager = 'manager';
    case Receptionist = 'receptionist';
    case Housekeeper = 'housekeeper';
}
