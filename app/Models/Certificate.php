<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'source',
        'external_id',
        'verification_code',
        'title',
        'issuer',
        'category',
        'description',
        'issued_on',
        'file_url',
        'thumbnail_url',
        'local_path',
        'tags',
        'sort_order',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'tags' => 'array',
            'is_featured' => 'boolean',
        ];
    }
}
