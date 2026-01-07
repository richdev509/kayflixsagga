<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::ordered()->get();
        return view('admin.subscription-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.subscription-plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'max_devices' => 'required|integer|min:1',
            'video_quality' => 'required|in:SD,HD,4K',
            'sort_order' => 'required|integer|min:0',
            'stripe_product_id' => 'nullable|string|max:255',
            'stripe_price_id' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['has_offline_download'] = $request->has('has_offline_download') ? true : false;

        SubscriptionPlan::create($validated);

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Plan d\'abonnement créé avec succès.');
    }

    public function show(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->load(['subscriptions' => function($query) {
            $query->latest()->take(10);
        }]);

        return view('admin.subscription-plans.show', compact('subscriptionPlan'));
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        return view('admin.subscription-plans.edit', compact('subscriptionPlan'));
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'max_devices' => 'required|integer|min:1',
            'video_quality' => 'required|in:SD,HD,4K',
            'sort_order' => 'required|integer|min:0',
            'stripe_product_id' => 'nullable|string|max:255',
            'stripe_price_id' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['has_offline_download'] = $request->has('has_offline_download') ? true : false;

        $subscriptionPlan->update($validated);

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Plan d\'abonnement mis à jour avec succès.');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        // Vérifier s'il y a des abonnements actifs
        $activeSubscriptions = $subscriptionPlan->activeSubscriptions()->count();

        if ($activeSubscriptions > 0) {
            return redirect()->route('admin.subscription-plans.index')
                ->with('error', "Impossible de supprimer ce plan. Il y a {$activeSubscriptions} abonnement(s) actif(s).");
        }

        $subscriptionPlan->delete();

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Plan d\'abonnement supprimé avec succès.');
    }

    public function toggle(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->update([
            'is_active' => !$subscriptionPlan->is_active
        ]);

        $status = $subscriptionPlan->is_active ? 'activé' : 'désactivé';

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', "Plan {$status} avec succès.");
    }
}
