<?php

declare(strict_types=1);

namespace Modules\Tenants\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenancy()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant not found.');
        }

        if (! $tenant->isActive()) {
            abort(403, $this->getStatusMessage($tenant->status));
        }

        return $next($request);
    }

    /**
     * Get the appropriate error message based on tenant status.
     */
    private function getStatusMessage(string $status): string
    {
        return match ($status) {
            'disabled' => 'This tenant account has been disabled. Please contact support.',
            default => 'Access denied.',
        };
    }
}
