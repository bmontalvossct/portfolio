<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkExperience extends Model
{
    protected $fillable = [
        'organization',
        'logo_url',
        'position',
        'start_date',
        'end_date',
        'location',
        'summary',
        'responsibilities',
        'sort_order',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'responsibilities' => 'array',
            'is_current' => 'boolean',
        ];
    }
}
