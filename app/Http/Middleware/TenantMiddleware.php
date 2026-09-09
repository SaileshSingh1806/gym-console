<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is super admin and on admin routes, bypass tenant scoping
        if ($user && $user->isSuperAdmin() && ($request->is('admin*') || $request->is('api/v1/admin*'))) {
            TenantContext::setBypass(true);

            return $next($request);
        }

        TenantContext::setBypass(false);

        $tenant = null;

        if ($user && $user->tenant_id) {
            $tenant = $user->tenant;
        } elseif ($request->header('X-Tenant-ID')) {
            $tenant = Tenant::find($request->header('X-Tenant-ID'));
        }

        if ($tenant) {
            TenantContext::setTenant($tenant);

            // Determine active branch
            $branchId = $request->header('X-Branch-ID') ?? session('active_branch_id');
            $branch = null;

            if ($branchId) {
                $branch = Branch::where('tenant_id', $tenant->id)->find($branchId);
            }

            if (! $branch) {
                $branch = $tenant->mainBranch ?? $tenant->branches()->first();
            }

            if ($branch) {
                TenantContext::setBranch($branch);
                if ($request->hasSession()) {
                    session(['active_branch_id' => $branch->id]);
                }
            }
        }

        return $next($request);
    }
}
