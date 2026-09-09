<?php

namespace App\Http\Middleware;

use App\Services\FeatureGateService;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeatureGateMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $featureCode): Response
    {
        $user = $request->user();

        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = TenantContext::getTenant();

        if (! $tenant) {
            return $next($request);
        }

        $hasFeature = app(FeatureGateService::class)->hasFeature($tenant, $featureCode);

        if (! $hasFeature) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => "The feature '{$featureCode}' is not included in your current subscription plan.",
                    'code' => 'FEATURE_NOT_AVAILABLE',
                ], 403);
            }

            return redirect()->route('subscription.index')->with('error', "The feature '{$featureCode}' is not available in your current plan. Please upgrade to unlock it.");
        }

        return $next($request);
    }
}
