<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $table = 'education';

    protected $fillable = [
        'institution',
        'logo_url',
        'program',
        'level',
        'start_date',
        'end_date',
        'location',
        'description',
        'activities',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'activities' => 'array',
        ];
    }
}
