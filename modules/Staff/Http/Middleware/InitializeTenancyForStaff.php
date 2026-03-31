<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Modules\Tenants\Domain\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyForStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (tenancy()->initialized) {
            return $next($request);
        }

        $user = $request->user();

        if ($user instanceof User) {
            $tenant = Tenant::where('owner_id', $user->id)->firstOrFail();

            try {
                tenancy()->initialize($tenant);
            } catch (\Exception $e) {
                // Tenant database not yet provisioned; proceed with central connection.
                // Staff data will be scoped correctly once the tenant DB is set up.
                logger()->warning('Could not initialize tenancy for staff management.', [
                    'tenant_id' => $tenant->getKey(),
                    'reason' => $e->getMessage(),
                ]);
            }
        }

        return $next($request);
    }
}
