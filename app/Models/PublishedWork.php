<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublishedWork extends Model
{
    protected $fillable = [
        'type',
        'title',
        'publication',
        'role',
        'summary',
        'published_on',
        'cover_url',
        'doi',
        'external_url',
        'tags',
        'sort_order',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'published_on' => 'date',
            'tags' => 'array',
            'is_featured' => 'boolean',
        ];
    }
}
