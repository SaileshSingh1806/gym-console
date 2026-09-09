<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireActiveSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            return $next($request);
        }

        // Allow access to subscription and billing routes even if expired
        if ($request->is('app/subscription*') || $request->is('api/v1/subscription*') || $request->is('logout')) {
            return $next($request);
        }

        if (! $tenant->isSubscriptionActive()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription expired or suspended. Please renew your SaaS subscription to continue.',
                    'code' => 'SUBSCRIPTION_EXPIRED',
                ], 402);
            }

            return redirect()->route('app.subscription.index')->with('warning', 'Your Free Trial / SaaS Subscription has expired. Please renew or upgrade your plan to continue using Gym Console.');
        }

        return $next($request);
    }
}
