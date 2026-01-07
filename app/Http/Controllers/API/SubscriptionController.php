<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Get all active subscription plans
     */
    public function plans()
    {
        $plans = SubscriptionPlan::active()
            ->ordered()
            ->get();

        return response()->json($plans);
    }

    /**
     * Subscribe user to platform
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:monthly,yearly',
        ]);

        $user = $request->user();

        // Check if user already has an active subscription
        $activeSubscription = $user->activeSubscription;

        if ($activeSubscription) {
            return response()->json([
                'message' => 'Vous avez déjà un abonnement actif',
                'subscription' => $activeSubscription,
            ], 400);
        }

        // Calculate dates and amount based on plan
        $startDate = now();
        $amount = $request->plan === 'monthly' ? 9.99 : 99.99;
        $endDate = $request->plan === 'monthly'
            ? $startDate->copy()->addMonth()
            : $startDate->copy()->addYear();

        // Create subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'amount' => $amount,
        ]);

        return response()->json([
            'message' => 'Abonnement créé avec succès',
            'subscription' => $subscription,
        ], 201);
    }

    /**
     * Get subscription status
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $activeSubscription = $user->activeSubscription;

        if (!$activeSubscription) {
            return response()->json([
                'has_subscription' => false,
                'message' => 'Aucun abonnement actif',
            ]);
        }

        return response()->json([
            'has_subscription' => true,
            'subscription' => $activeSubscription,
            'days_remaining' => now()->diffInDays($activeSubscription->end_date),
        ]);
    }
}

