<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthToken extends Model
{
    protected $table = 'oauth_tokens';

    protected $fillable = [
        'provider',
        'access_token',
        'refresh_token',
        'expires_in',
        'expires_at',
        'token_type',
        'scope',
        'auth_method',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to get active tokens only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get tokens by provider
     */
    public function scopeProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Check if token is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if token is valid (active and not expired)
     */
    public function isValid()
    {
        return $this->is_active && !$this->isExpired();
    }
}

