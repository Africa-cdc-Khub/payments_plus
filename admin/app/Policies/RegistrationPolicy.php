<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Registration;

class RegistrationPolicy
{
    /**
     * Determine if the user can view any registrations.
     */
    public function viewAny(Admin $admin): bool
    {
        return in_array($admin->role, ['admin', 'secretariat', 'finance', 'hosts']);
    }

    /**
     * Determine if the user can view the registration.
     */
    public function view(Admin $admin, Registration $registration): bool
    {
        return in_array($admin->role, ['admin', 'secretariat', 'finance', 'hosts']);
    }

    /**
     * Determine if the user can manage delegates (approve/reject).
     */
    public function manageDelegates(Admin $admin): bool
    {
        return in_array($admin->role, ['admin', 'secretariat']);
    }

    /**
     * Determine if the user can view/download invitations.
     */
    public function viewInvitation(Admin $admin): bool
    {
        return in_array($admin->role, ['admin', 'secretariat', 'finance', 'travels', 'hosts']);
    }

    /**
     * Determine if the user can send invitations.
     */
    public function sendInvitation(Admin $admin): bool
    {
        return in_array($admin->role, ['admin', 'secretariat']);
    }

    /**
     * Determine if the user can mark registrations as paid.
     */
    public function markAsPaid(Admin $admin): bool
    {
        return in_array($admin->role, ['admin', 'finance']);
    }
}

