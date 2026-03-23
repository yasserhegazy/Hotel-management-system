<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Modules\Staff\Domain\Enums\StaffPermission;
use Modules\Staff\Domain\Models\TenantUser;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanManageStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Owner (User model via sanctum) has full access to staff management
        if ($user instanceof User) {
            return $next($request);
        }

        // Staff (TenantUser) needs manage_staff permission
        if ($user instanceof TenantUser && $user->can(StaffPermission::ManageStaff->value)) {
            return $next($request);
        }

        abort(403, 'You do not have permission to manage staff.');
    }
}
