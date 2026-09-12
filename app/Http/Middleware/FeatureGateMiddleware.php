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
    public function handle(Request $request, Closure $next, string ...$featureCodes): Response
    {
        $user = $request->user();

        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = TenantContext::getTenant() ?? $user?->tenant;

        if (! $tenant) {
            return $next($request);
        }

        $featureGateService = app(FeatureGateService::class);
        $hasAnyFeature = false;

        foreach ($featureCodes as $featureCode) {
            if ($featureGateService->hasFeature($tenant, $featureCode)) {
                $hasAnyFeature = true;
                break;
            }
        }

        if (! $hasAnyFeature) {
            $featureDisplay = implode(' or ', $featureCodes);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => "The requested feature ({$featureDisplay}) is not included in your current subscription plan.",
                    'code' => 'FEATURE_NOT_AVAILABLE',
                ], 403);
            }

            return redirect()->route('app.subscription.index')->with('error', "The requested feature ({$featureDisplay}) is not included in your current subscription plan. Please upgrade your plan to unlock it.");
        }

        return $next($request);
    }
}
