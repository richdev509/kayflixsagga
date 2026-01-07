<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchLog extends Model
{
    protected $fillable = [
        'user_id',
        'video_id',
        'seconds_watched',
    ];

    protected $casts = [
        'seconds_watched' => 'integer',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
