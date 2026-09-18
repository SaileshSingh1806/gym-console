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

        // Allow access to subscription, checkout, and logout routes even if unpaid or expired
        if ($request->is('app/subscription*') || $request->is('api/v1/subscription*') || $request->is('checkout') || $request->is('logout')) {
            return $next($request);
        }

        if (! $tenant->isSubscriptionActive()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription payment required or expired. Please complete payment to continue.',
                    'code' => 'SUBSCRIPTION_REQUIRED',
                ], 402);
            }

            if ($tenant->status === 'PENDING_PAYMENT' || ! $tenant->activeSubscription) {
                return redirect()->route('auth.checkout')->with('warning', 'Please complete your subscription payment to access your gym dashboard.');
            }

            return redirect()->route('app.subscription.index')->with('warning', 'Your SaaS Subscription has expired. Please renew or upgrade your plan to continue using Gym Console.');
        }

        return $next($request);
    }
}
