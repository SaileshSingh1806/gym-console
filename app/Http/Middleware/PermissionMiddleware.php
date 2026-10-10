<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        if ($user->isSuperAdmin() || $user->isGymOwner()) {
            return $next($request);
        }

        if ($user->hasAnyPermission($permissions)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have the required permissions to perform this action.',
            ], 403);
        }

        abort(403, 'You do not have permission to access this resource or perform this action.');
    }
}
