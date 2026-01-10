<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip for admins
        if ($user && $user->hasRole('admin')) {
            return $next($request);
        }

        // Check if user has active subscription
        $hasActiveSubscription = $user && $user->activeSubscription()->exists();

        if (!$hasActiveSubscription) {
            return response()->json([
                'message' => 'Abonnement actif requis. Veuillez renouveler votre abonnement.',
                'subscription_expired' => true,
                'requires_payment' => true,
            ], 403);
        }

        return $next($request);
    }
}
