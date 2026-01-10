<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        // Debug: Log user info
        \Log::info('Getting subscription status', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // Récupérer tous les abonnements de l'utilisateur
        $allSubscriptions = $user->subscriptions()->get();
        \Log::info('All user subscriptions', [
            'count' => $allSubscriptions->count(),
            'subscriptions' => $allSubscriptions->toArray(),
        ]);

        // Chercher un abonnement actif ou annulé mais encore valide
        $subscription = $user->subscriptions()
            ->whereIn('status', ['active', 'canceled'])
            ->where('expires_at', '>=', now())
            ->orderBy('expires_at', 'desc')
            ->first();

        if (!$subscription) {
            \Log::info('No active subscription found for user ' . $user->id);
            return response()->json([
                'has_active_subscription' => false,
                'message' => 'Aucun abonnement actif',
            ]);
        }

        \Log::info('Found subscription', [
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'expires_at' => $subscription->expires_at,
        ]);

        return response()->json([
            'has_active_subscription' => $subscription->status === 'active',
            'subscription' => $subscription->load('plan'),
            'days_remaining' => now()->diffInDays($subscription->expires_at),
            'will_renew' => $subscription->status === 'active',
            'cancel_at_period_end' => $subscription->status === 'canceled',
        ]);
    }

    /**
     * Cancel subscription (at period end)
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>=', now())
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Aucun abonnement actif à annuler',
            ], 404);
        }

        if (!$subscription->stripe_subscription_id) {
            return response()->json([
                'message' => 'Impossible d\'annuler cet abonnement',
            ], 400);
        }

        try {
            // Annuler sur Stripe (à la fin de la période)
            $stripe = new \Stripe\StripeClient(config('stripe.secret'));
            $stripe->subscriptions->update(
                $subscription->stripe_subscription_id,
                ['cancel_at_period_end' => true]
            );

            // Mettre à jour le statut local
            $subscription->update(['status' => 'canceled']);

            return response()->json([
                'message' => 'Abonnement annulé. Vous gardez l\'accès jusqu\'au ' . $subscription->expires_at->format('d/m/Y'),
                'subscription' => $subscription->load('plan'),
                'access_until' => $subscription->expires_at,
            ]);
        } catch (\Exception $e) {
            Log::error('Subscription cancellation error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reactivate a canceled subscription
     */
    public function reactivate(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscriptions()
            ->where('status', 'canceled')
            ->where('expires_at', '>=', now())
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Aucun abonnement annulé à réactiver',
            ], 404);
        }

        if (!$subscription->stripe_subscription_id) {
            return response()->json([
                'message' => 'Impossible de réactiver cet abonnement',
            ], 400);
        }

        try {
            // Réactiver sur Stripe
            $stripe = new \Stripe\StripeClient(config('stripe.secret'));
            $stripe->subscriptions->update(
                $subscription->stripe_subscription_id,
                ['cancel_at_period_end' => false]
            );

            // Mettre à jour le statut local
            $subscription->update(['status' => 'active']);

            return response()->json([
                'message' => 'Abonnement réactivé. Le renouvellement automatique reprendra.',
                'subscription' => $subscription->load('plan'),
            ]);
        } catch (\Exception $e) {
            Log::error('Subscription reactivation error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la réactivation: ' . $e->getMessage(),
            ], 500);
        }
    }
}

