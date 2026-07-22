<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CredentialBadge extends Model
{
    protected $fillable = [
        'provider',
        'external_id',
        'name',
        'issuer',
        'category',
        'description',
        'issued_at',
        'expires_at',
        'image_url',
        'certificate_url',
        'criteria_url',
        'evidence_url',
        'skills',
        'sort_order',
        'is_featured',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'skills' => 'array',
            'is_featured' => 'boolean',
            'synced_at' => 'datetime',
        ];
    }
}
