<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignMedia extends Model
{
    protected $table = 'design_media';

    protected $fillable = [
        'title',
        'media_type',
        'description',
        'year',
        'media_url',
        'thumbnail_url',
        'external_url',
        'sort_order',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
        ];
    }
}
