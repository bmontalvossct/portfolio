<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = [
        'title',
        'issuer',
        'summary',
        'achieved_on',
        'image_url',
        'external_url',
        'tags',
        'sort_order',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'achieved_on' => 'date',
            'tags' => 'array',
            'is_featured' => 'boolean',
        ];
    }
}
