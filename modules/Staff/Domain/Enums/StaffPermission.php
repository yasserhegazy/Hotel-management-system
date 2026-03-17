<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Enums;

enum StaffPermission: string
{
    case ManageStaff = 'manage_staff';
    case ViewStaff = 'view_staff';
    case ManageBookings = 'manage_bookings';
    case ManageUnits = 'manage_units';
    case ViewUnits = 'view_units';
    case UpdateUnitStatus = 'update_unit_status';
    case CheckIn = 'check_in';
    case CheckOut = 'check_out';
    case ViewReports = 'view_reports';
}
