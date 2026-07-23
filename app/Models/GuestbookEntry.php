<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestbookEntry extends Model
{
    protected $fillable = [
        'public_id',
        'name',
        'email',
        'role_or_organization',
        'body',
        'rating',
        'status',
        'notification_status',
        'admin_reply',
        'approved_at',
        'replied_at',
        'notification_attempted_at',
        'notification_accepted_at',
        'notification_error',
        'ip_hash',
    ];

    protected $hidden = [
        'email',
        'ip_hash',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'approved_at' => 'datetime',
            'replied_at' => 'datetime',
            'notification_attempted_at' => 'datetime',
            'notification_accepted_at' => 'datetime',
        ];
    }
}
