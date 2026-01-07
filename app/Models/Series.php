<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Series extends Model
{
    protected $fillable = [
        'creator_id',
        'title',
        'description',
        'thumbnail_url',
        'banner_url',
        'trailer_url',
        'bunny_trailer_id',
        'category',
        'release_year',
        'is_published',
        'total_seasons',
        'views_count',
        'rating',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'release_year' => 'integer',
        'total_seasons' => 'integer',
        'views_count' => 'integer',
        'rating' => 'decimal:1',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }
}
