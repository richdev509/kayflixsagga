<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;
use Stripe\BillingPortal\Session as BillingPortalSession;
use Stripe\Stripe;

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
        Log::info('Getting subscription status', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // Récupérer tous les abonnements de l'utilisateur
        $allSubscriptions = $user->subscriptions()->get();
        Log::info('All user subscriptions', [
            'count' => $allSubscriptions->count(),
            'subscriptions' => $allSubscriptions->toArray(),
        ]);

        // Chercher un abonnement actif ou annulé mais encore valide
        $subscription = $user->subscriptions()
            ->whereIn('status', ['active', 'cancelled'])
            ->where('expires_at', '>=', now())
            ->orderBy('expires_at', 'desc')
            ->first();

        if (!$subscription) {
            Log::info('No active subscription found for user ' . $user->id);
            return response()->json([
                'has_active_subscription' => false,
                'message' => 'Aucun abonnement actif',
            ]);
        }

        Log::info('Found subscription', [
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'expires_at' => $subscription->expires_at,
        ]);

        return response()->json([
            'has_active_subscription' => true, // L'utilisateur a accès tant que expires_at est valide
            'subscription' => $subscription->load('plan'),
            'days_remaining' => now()->diffInDays($subscription->expires_at),
            'will_renew' => $subscription->status === 'active',
            'cancel_at_period_end' => $subscription->status === 'cancelled',
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
            ->where('status', 'cancelled')
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

    /**
     * Create checkout session for subscription upgrade
     */
    public function upgrade(Request $request)
    {
        Stripe::setApiKey(config('stripe.secret'));

        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $user = $request->user();
        $newPlan = SubscriptionPlan::findOrFail($request->plan_id);

        if (!$newPlan->stripe_price_id) {
            return response()->json([
                'message' => 'Ce plan n\'a pas de prix Stripe configuré',
            ], 400);
        }

        try {
            // Créer une session Stripe Checkout pour l'upgrade
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $newPlan->stripe_price_id,
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => config('app.url') . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.url') . '/payment/cancel',
                'customer_email' => $user->email,
                'client_reference_id' => $user->id,
                'metadata' => [
                    'user_id' => $user->id,
                    'plan_id' => $newPlan->id,
                    'upgrade' => 'true',
                ],
            ]);

            Log::info('Upgrade checkout session created', [
                'user_id' => $user->id,
                'plan_id' => $newPlan->id,
                'session_id' => $session->id,
            ]);

            return response()->json([
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Upgrade checkout error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la création de la session de paiement',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create Stripe Customer Portal session for payment method management
     */
    public function createPortalSession(Request $request)
    {
        Stripe::setApiKey(config('stripe.secret'));

        $user = $request->user();

        if (!$user->stripe_customer_id) {
            return response()->json([
                'message' => 'Aucun compte Stripe associé à cet utilisateur',
            ], 400);
        }

        try {
            // Créer une session du portail client Stripe
            $session = BillingPortalSession::create([
                'customer' => $user->stripe_customer_id,
                'return_url' => config('app.frontend_url', 'https://mykayflix.com') . '/subscription/success',
            ]);

            Log::info('Customer portal session created', [
                'user_id' => $user->id,
                'customer_id' => $user->stripe_customer_id,
                'session_id' => $session->id,
            ]);

            return response()->json([
                'portal_url' => $session->url,
            ]);
        } catch (\Exception $e) {
            Log::error('Customer portal error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la création du portail de gestion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
