<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'creator_id',
        'bunny_video_id',
        'title',
        'description',
        'thumbnail_url',
        'status',
        'is_published',
        'duration',
        'category',
        'views_count',
    ];

    protected $casts = [
        'duration' => 'integer',
        'views_count' => 'integer',
        'is_published' => 'boolean',
    ];

    // Relations
    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }

    public function watchLogs()
    {
        return $this->hasMany(WatchLog::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
