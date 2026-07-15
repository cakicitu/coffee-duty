<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningDuty extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'week_start',
        'done',
        'done_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'week_start' => 'date:Y-m-d',
            'done' => 'boolean',
            'done_at' => 'datetime:Y-m-d H:i',
        ];
    }

    // The user assigned to clean the machine in this week
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
