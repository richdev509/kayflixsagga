<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\PendingRegistration;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret'));
    }

    /**
     * Handle Stripe webhooks
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('stripe.webhook_secret');

        // En développement, désactiver la vérification si pas de secret configuré
        if (empty($webhookSecret) || $webhookSecret === 'your_webhook_secret_here') {
            Log::info('Webhook signature verification skipped (development mode)');
            $event = json_decode($payload);
        } else {
            try {
                $event = Webhook::constructEvent(
                    $payload,
                    $sigHeader,
                    $webhookSecret
                );
            } catch (\Exception $e) {
                Log::error('Webhook signature verification failed: ' . $e->getMessage());
                return response()->json(['error' => 'Webhook signature verification failed'], 400);
            }
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;

            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event->data->object);
                break;

            default:
                Log::info('Unhandled webhook event type: ' . $event->type);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle checkout.session.completed event
     * This is where we create the user account and subscription
     */
    private function handleCheckoutSessionCompleted($session)
    {
        Log::info('Checkout session completed', ['session_id' => $session->id]);

        // Trouver la registration pending
        $pendingReg = PendingRegistration::where('stripe_session_id', $session->id)->first();

        if (!$pendingReg) {
            Log::error('Pending registration not found', ['session_id' => $session->id]);
            return;
        }

        // Check if user already exists
        $user = User::where('email', $pendingReg->email)->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'name' => $pendingReg->name,
                'email' => $pendingReg->email,
                'password' => $pendingReg->password, // Already hashed
            ]);

            Log::info('User created from Stripe webhook', ['user_id' => $user->id]);
        }

        // Get subscription plan
        $plan = SubscriptionPlan::find($pendingReg->plan_id);

        if (!$plan) {
            Log::error('Subscription plan not found', ['plan_id' => $pendingReg->plan_id]);
            return;
        }

        // Create subscription
        $startDate = now();
        $endDate = now()->addDays($plan->duration_days);

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'stripe_subscription_id' => $session->subscription,
            'stripe_customer_id' => $session->customer,
            'start_date' => $startDate,
            'expires_at' => $endDate,
            'status' => 'active',
            'amount' => $plan->price,
        ]);

        // Marquer la registration comme complétée
        $pendingReg->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Log::info('Subscription created from Stripe webhook', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'stripe_subscription_id' => $session->subscription
        ]);
    }

    /**
     * Handle subscription.updated event
     */
    private function handleSubscriptionUpdated($stripeSubscription)
    {
        Log::info('Subscription updated', ['subscription' => $stripeSubscription]);

        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscription) {
            $subscription->update([
                'status' => $stripeSubscription->status,
            ]);
        }
    }

    /**
     * Handle subscription.deleted event
     */
    private function handleSubscriptionDeleted($stripeSubscription)
    {
        Log::info('Subscription deleted', ['subscription' => $stripeSubscription]);

        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
            ]);
        }
    }

    /**
     * Handle invoice.payment_succeeded event
     */
    private function handleInvoicePaymentSucceeded($invoice)
    {
        Log::info('Invoice payment succeeded', ['invoice_id' => $invoice->id, 'subscription_id' => $invoice->subscription]);

        // Si c'est le premier paiement (billing_reason = subscription_create), créer le compte
        if ($invoice->billing_reason === 'subscription_create') {
            // Récupérer la subscription Stripe
            $stripeSubscriptionId = $invoice->subscription;

            // Chercher le pending registration via customer_email
            $pendingReg = PendingRegistration::where('email', $invoice->customer_email)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$pendingReg) {
                Log::error('Pending registration not found for invoice', [
                    'customer_email' => $invoice->customer_email,
                    'invoice_id' => $invoice->id
                ]);
                return;
            }

            // Vérifier si l'utilisateur existe déjà
            $user = User::where('email', $pendingReg->email)->first();

            if (!$user) {
                // Créer le user
                $user = User::create([
                    'name' => $pendingReg->name,
                    'email' => $pendingReg->email,
                    'password' => $pendingReg->password,
                ]);

                Log::info('User created from invoice webhook', ['user_id' => $user->id]);
            }

            // Get subscription plan
            $plan = SubscriptionPlan::find($pendingReg->plan_id);

            if (!$plan) {
                Log::error('Subscription plan not found', ['plan_id' => $pendingReg->plan_id]);
                return;
            }

            // Créer la subscription
            $startDate = now();
            $endDate = now()->addDays($plan->duration_days);

            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'stripe_subscription_id' => $stripeSubscriptionId,
                'stripe_customer_id' => $invoice->customer,
                'start_date' => $startDate,
                'expires_at' => $endDate,
                'status' => 'active',
                'amount' => $plan->price,
            ]);

            // Marquer le pending registration comme complété
            $pendingReg->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Log::info('Subscription created from invoice webhook', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'stripe_subscription_id' => $stripeSubscriptionId
            ]);
        } else {
            // Renouvellement : prolonger l'abonnement existant
            $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();

            if ($subscription) {
                $plan = $subscription->plan;
                $subscription->update([
                    'expires_at' => now()->addDays($plan->duration_days),
                    'status' => 'active',
                ]);

                Log::info('Subscription renewed', ['subscription_id' => $subscription->id]);
            }
        }
    }

    /**
     * Handle invoice.payment_failed event
     */
    private function handleInvoicePaymentFailed($invoice)
    {
        Log::error('Invoice payment failed', ['invoice' => $invoice]);

        $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'payment_failed',
            ]);
        }
    }
}
