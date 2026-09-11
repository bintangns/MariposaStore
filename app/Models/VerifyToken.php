<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifyToken extends Model
{
    protected $fillable = [
        'minecraft_username',
        'token',
        'verified',
        'expires_at',
        'ip_address',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function scopeValid($query)
    {
        return $query->where('verified', false)
                     ->where('expires_at', '>', now());
    }
}
