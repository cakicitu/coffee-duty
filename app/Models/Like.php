<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    protected $fillable = [
        'user_id',
        'bean_id',
    ];

    // The user who gave this rating
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // The bean pack this rating belongs to
    public function bean(): BelongsTo
    {
        return $this->belongsTo(Bean::class);
    }
}
