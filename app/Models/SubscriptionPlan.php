<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'stripe_price_id',
        'stripe_product_id',
        'duration_days',
        'features',
        'is_active',
        'max_devices',
        'has_offline_download',
        'video_quality',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'max_devices' => 'integer',
        'has_offline_download' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relations
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id')
            ->where('status', 'active')
            ->where('end_date', '>=', now());
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2, ',', ' ') . ' $';
    }
}
