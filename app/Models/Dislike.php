<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dislike extends Model
{
    protected $fillable = [
        'user_id',
        'bean_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function bean(): BelongsTo
    {
        return $this->belongsTo(Bean::class, 'id');
    }
}
