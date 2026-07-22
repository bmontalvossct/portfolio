<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'display_name',
        'slug',
        'headline',
        'availability',
        'location',
        'email',
        'bio',
        'avatar_url',
        'credly_username',
        'github_username',
        'canva_url',
        'external_links',
        'highlights',
        'skills',
        'stats',
    ];

    protected function casts(): array
    {
        return [
            'external_links' => 'array',
            'highlights' => 'array',
            'skills' => 'array',
            'stats' => 'array',
        ];
    }
}
