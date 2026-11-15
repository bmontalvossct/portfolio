<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title',
        'category',
        'summary',
        'year',
        'url',
        'repo_url',
        'thumbnail_url',
        'gallery_urls',
        'tags',
        'sort_order',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'gallery_urls' => 'array',
            'is_featured' => 'boolean',
        ];
    }
}
