<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioTool extends Model
{
    protected $fillable = [
        'name',
        'category',
        'description',
        'icon_url',
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
