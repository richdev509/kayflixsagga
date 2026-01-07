<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Episode extends Model
{
    protected $fillable = [
        'season_id',
        'episode_number',
        'title',
        'description',
        'bunny_video_id',
        'thumbnail_url',
        'duration',
        'is_published',
        'views_count',
    ];

    protected $casts = [
        'season_id' => 'integer',
        'episode_number' => 'integer',
        'duration' => 'integer',
        'is_published' => 'boolean',
        'views_count' => 'integer',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}
