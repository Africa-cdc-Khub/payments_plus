<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The table associated with the model.
     */
    protected $table = 'admins';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if admin is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if admin is finance team
     */
    public function isFinanceTeam(): bool
    {
        return $this->hasRole('finance_team');
    }

    /**
     * Check if admin is visa team
     */
    public function isVisaTeam(): bool
    {
        return $this->hasRole('visa_team');
    }

    /**
     * Check if admin is ticketing team
     */
    public function isTicketingTeam(): bool
    {
        return $this->hasRole('ticketing_team');
    }

    /**
     * Scope to get only active admins
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
