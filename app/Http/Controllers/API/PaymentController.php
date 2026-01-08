<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\PendingRegistration;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret'));
    }

    /**
     * Create Stripe Checkout Session for subscription
     */
    public function createCheckoutSession(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        if (!$plan->stripe_price_id) {
            return response()->json([
                'error' => 'Ce plan n\'a pas de prix Stripe configuré'
            ], 400);
        }

        try {
            // Hasher le mot de passe
            $hashedPassword = bcrypt($request->password);

            // Créer une session Stripe Checkout
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $plan->stripe_price_id,
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => config('app.url') . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.url') . '/payment/cancel',
                'customer_email' => $request->email,
                'metadata' => [
                    'plan_id' => $plan->id,
                    'user_name' => $request->name,
                    'user_email' => $request->email,
                    'user_password' => $hashedPassword,
                ],
            ]);

            // Créer une entrée pending registration
            PendingRegistration::create([
                'stripe_session_id' => $session->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => $hashedPassword,
                'plan_id' => $plan->id,
                'status' => 'pending',
            ]);

            return response()->json([
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la création de la session de paiement',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify payment success and auto-login user
     */
    public function verifySession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        try {
            // Vérifier si la registration est en pending
            $pendingReg = PendingRegistration::where('stripe_session_id', $request->session_id)->first();

            if (!$pendingReg) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session non trouvée'
                ], 404);
            }

            $session = StripeSession::retrieve($request->session_id);

            if ($session->payment_status === 'paid') {
                // Essayer de trouver l'utilisateur avec plusieurs tentatives (max 10 secondes)
                $maxAttempts = 5;
                $user = null;

                for ($i = 0; $i < $maxAttempts; $i++) {
                    $user = \App\Models\User::where('email', $pendingReg->email)->first();

                    if ($user) {
                        // Marquer la registration comme complétée
                        $pendingReg->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                        break;
                    }

                    // Attendre 2 secondes avant la prochaine tentative
                    sleep(2);
                }

                if ($user) {
                    // Créer un token d'authentification
                    $token = $user->createToken('auth_token')->plainTextToken;

                    return response()->json([
                        'success' => true,
                        'user' => $user,
                        'token' => $token,
                        'subscription' => $user->subscriptions()->where('status', 'active')->first(),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'pending' => true,
                    'message' => 'Paiement confirmé, compte en cours de création',
                    'registration' => [
                        'name' => $pendingReg->name,
                        'email' => $pendingReg->email,
                        'plan' => $pendingReg->plan,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Le paiement n\'a pas été complété'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la vérification du paiement',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a pending registration session
     */
    public function cancelSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        try {
            $pendingReg = PendingRegistration::where('stripe_session_id', $request->session_id)
                ->where('status', 'pending')
                ->first();

            if ($pendingReg) {
                $pendingReg->update([
                    'status' => 'cancelled',
                    'completed_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Session annulée avec succès',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Session non trouvée ou déjà traitée',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l\'annulation',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
