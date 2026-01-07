<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Creator extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'bio',
        'channel_name',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }
}
