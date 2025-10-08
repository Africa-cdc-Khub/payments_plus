<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'role',
        'is_active',
        'last_login',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSecretariat(): bool
    {
        return $this->role === 'secretariat';
    }

    public function isFinance(): bool
    {
        return $this->role === 'finance';
    }

    public function isExecutive(): bool
    {
        return $this->role === 'executive';
    }

    public function canManageDelegates(): bool
    {
        return in_array($this->role, ['admin', 'secretariat']);
    }

    public function canViewInvitations(): bool
    {
        return in_array($this->role, ['admin', 'secretariat', 'executive']);
    }

    public function canSendInvitations(): bool
    {
        return in_array($this->role, ['admin', 'secretariat']);
    }
}

