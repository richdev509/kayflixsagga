<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorPayout extends Model
{
    protected $fillable = [
        'creator_id',
        'month',
        'year',
        'minutes_watched',
        'total_platform_minutes',
        'revenue_share_percentage',
        'amount',
        'status',
        'paid_at',
        'stripe_transfer_id',
        'notes',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'minutes_watched' => 'decimal:2',
        'total_platform_minutes' => 'decimal:2',
        'revenue_share_percentage' => 'decimal:2',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    /**
     * Scope pour les paiements en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour les paiements payés
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Marquer comme payé
     */
    public function markAsPaid(string $stripeTransferId = null)
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'stripe_transfer_id' => $stripeTransferId,
        ]);
    }
}

