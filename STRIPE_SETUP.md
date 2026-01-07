# Configuration Stripe pour les abonnements

## 1. Créer un compte Stripe

1. Allez sur https://stripe.com et créez un compte
2. Accédez au Dashboard Stripe

## 2. Récupérer les clés API

1. Dans le Dashboard, allez dans **Developers** > **API keys**
2. Copiez les clés suivantes :
   - **Publishable key** (commence par `pk_test_...`)
   - **Secret key** (commence par `sk_test_...`)

## 3. Créer les produits et prix dans Stripe

### Option 1 : Via le Dashboard Stripe (Recommandé)

1. Allez dans **Products** > **Add product**
2. Pour chaque plan (Basic, Premium, VIP) :
   - Nom : ex. "Plan Premium"
   - Description : ex. "Accès complet HD"
   - Prix : ex. 9.99 USD
   - **Recurring** : Cochez cette option
   - **Billing period** : Monthly ou Yearly
   - Cliquez sur **Save product**
3. Copiez le **Price ID** (commence par `price_...`)

### Option 2 : Via l'API (Automatique)

```php
// Dans tinker : php artisan tinker
use App\Models\SubscriptionPlan;

$stripe = new \Stripe\StripeClient(config('stripe.secret'));

// Pour chaque plan dans votre base de données
$plans = SubscriptionPlan::all();

foreach ($plans as $plan) {
    // Créer le produit
    $product = $stripe->products->create([
        'name' => $plan->name,
        'description' => $plan->description,
    ]);
    
    // Créer le prix récurrent
    $price = $stripe->prices->create([
        'product' => $product->id,
        'unit_amount' => $plan->price * 100, // En centimes
        'currency' => 'usd',
        'recurring' => [
            'interval' => $plan->duration_days == 30 ? 'month' : 'year',
        ],
    ]);
    
    // Mettre à jour le plan avec le price_id
    $plan->update(['stripe_price_id' => $price->id]);
    
    echo "Plan {$plan->name}: {$price->id}\n";
}
```

## 4. Configurer le fichier .env

Ajoutez ces lignes à votre fichier `.env` :

```env
# Stripe Configuration
STRIPE_KEY=pk_test_VOTRE_CLE_PUBLISHABLE
STRIPE_SECRET=sk_test_VOTRE_CLE_SECRET
STRIPE_WEBHOOK_SECRET=whsec_VOTRE_SECRET_WEBHOOK
```

## 5. Configurer le Webhook Stripe

1. Dans le Dashboard Stripe, allez dans **Developers** > **Webhooks**
2. Cliquez sur **Add endpoint**
3. URL du endpoint : `https://votre-domaine.com/api/stripe/webhook`
   - En local : Utilisez **Stripe CLI** (voir ci-dessous)
4. Sélectionnez les événements à écouter :
   - `checkout.session.completed`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`
5. Copiez le **Signing secret** (commence par `whsec_...`)
6. Ajoutez-le dans `.env` : `STRIPE_WEBHOOK_SECRET=whsec_...`

### Test en local avec Stripe CLI

```bash
# Installer Stripe CLI
# Windows : https://github.com/stripe/stripe-cli/releases

# Se connecter
stripe login

# Écouter les webhooks
stripe listen --forward-to localhost:8000/api/stripe/webhook

# Copier le webhook signing secret affiché et l'ajouter dans .env
```

## 6. Mettre à jour les plans avec stripe_price_id

Dans l'interface admin :
1. Allez dans **Plans d'abonnement**
2. Pour chaque plan, cliquez sur **Modifier**
3. Ajoutez le champ `stripe_price_id` avec la valeur copiée de Stripe (ex: `price_1234567890`)
4. Sauvegardez

Ou via SQL :

```sql
UPDATE subscription_plans SET stripe_price_id = 'price_XXXXX' WHERE name = 'Basic';
UPDATE subscription_plans SET stripe_price_id = 'price_YYYYY' WHERE name = 'Premium';
UPDATE subscription_plans SET stripe_price_id = 'price_ZZZZZ' WHERE name = 'VIP';
```

## 7. Tester le flow de paiement

1. Lancez le serveur Laravel : `php artisan serve`
2. Lancez l'app Flutter
3. Allez sur l'écran d'inscription
4. Sélectionnez un plan
5. Remplissez le formulaire
6. Cliquez sur **S'inscrire**
7. Une page Stripe s'ouvre dans le navigateur
8. Utilisez une carte de test :
   - **Numéro** : `4242 4242 4242 4242`
   - **Date** : N'importe quelle date future
   - **CVC** : N'importe quel code à 3 chiffres
9. Confirmez le paiement
10. Le webhook crée automatiquement le compte et l'abonnement
11. L'utilisateur peut se connecter avec ses identifiants

## 8. URLs de redirection (à configurer plus tard)

Pour production, configurez :
- `success_url` : Page de confirmation dans votre app
- `cancel_url` : Retour à l'inscription

## 9. Vérifier les webhooks

Consultez les logs :
```bash
tail -f storage/logs/laravel.log
```

Vous devriez voir :
```
Checkout session completed
User created from Stripe webhook
Subscription created from Stripe webhook
```

## 10. Cartes de test Stripe

- **Succès** : `4242 4242 4242 4242`
- **Échec** : `4000 0000 0000 0002`
- **Authentification 3D Secure** : `4000 0027 6000 3184`

## Support

- Documentation Stripe : https://stripe.com/docs
- Webhooks : https://stripe.com/docs/webhooks
- Subscriptions : https://stripe.com/docs/billing/subscriptions/overview
