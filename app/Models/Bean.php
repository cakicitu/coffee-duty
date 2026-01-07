<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; 

class Bean extends Model
{
     /** @use HasFactory<\Database\Factories\UserFactory> */
    // use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'count',
        'finished',
        'finished_at',
    ];

    protected $appends = ['lasted'];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
                'finished_at' => 'datetime:Y-m-d H:i',
                'created_at' => 'datetime:Y-m-d H:i',
                'updated_at' => 'datetime:Y-m-d H:i',
            ];
    }

    protected function lasted(): Attribute 
    {
        return Attribute::get(function () {
            $endDate = $this->finished_at ?? now();
            return round($this->created_at->diffInDays($endDate), 2);
        });
    }
}
