<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = [
        'creator_id',
        'month',
        'amount',
        'total_watch_time',
        'percentage',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'total_watch_time' => 'integer',
        'percentage' => 'decimal:2',
    ];

    // Relations
    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
