<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Tenant;
use App\Services\FeatureGateService;
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
        } elseif ($user && $user->isSuperAdmin()) {
            $tenantId = session('active_tenant_id') ?? session('switch_tenant_id') ?? $request->query('tenant_id');
            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
            }
            if (! $tenant) {
                $tenant = Tenant::first();
            }
        }

        if ($tenant) {
            TenantContext::setTenant($tenant);
            if ($request->hasSession()) {
                session(['active_tenant_id' => $tenant->id]);
            }

            $gate = app(FeatureGateService::class);
            $allowedBranches = $gate->getAllowedBranches($tenant);
            $allowedBranchIds = $allowedBranches->pluck('id')->all();

            // Determine active branch - only allowed branches under the current plan can be active
            $branchId = $request->header('X-Branch-ID') ?? session('active_branch_id');
            $branch = null;

            if ($branchId && in_array((int) $branchId, $allowedBranchIds, true)) {
                $branch = $allowedBranches->firstWhere('id', (int) $branchId);
            }

            if (! $branch) {
                $branch = $allowedBranches->firstWhere('is_main', true) ?? $allowedBranches->first();
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
