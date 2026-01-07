<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViewAnalytic extends Model
{
    protected $fillable = [
        'user_id',
        'video_id',
        'series_id',
        'episode_id',
        'started_at',
        'ended_at',
        'duration_watched',
        'completed',
        'device_type',
        'ip_address',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'completed' => 'boolean',
        'duration_watched' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * Scope pour obtenir les analytics d'un mois spécifique
     */
    public function scopeForMonth($query, int $month, int $year)
    {
        return $query->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year);
    }

    /**
     * Scope pour obtenir les analytics d'un creator
     */
    public function scopeForCreator($query, int $creatorId)
    {
        return $query->where(function($q) use ($creatorId) {
            $q->whereHas('video', function($subQ) use ($creatorId) {
                $subQ->where('creator_id', $creatorId);
            })->orWhereHas('series', function($subQ) use ($creatorId) {
                $subQ->where('creator_id', $creatorId);
            });
        });
    }
}

