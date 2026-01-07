<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    protected $fillable = [
        'series_id',
        'season_number',
        'title',
        'description',
        'thumbnail_url',
        'total_episodes',
        'release_year',
    ];

    protected $casts = [
        'series_id' => 'integer',
        'season_number' => 'integer',
        'total_episodes' => 'integer',
        'release_year' => 'integer',
    ];

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }
}
