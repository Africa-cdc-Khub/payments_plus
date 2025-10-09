<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Payment;

class PaymentPolicy
{
    /**
     * Determine if the user can view any payments.
     */
    public function viewAny(Admin $admin): bool
    {
        return in_array($admin->role, ['admin', 'secretariat', 'finance', 'executive']);
    }

    /**
     * Determine if the user can view the payment.
     */
    public function view(Admin $admin, Payment $payment): bool
    {
        // Executive can only view completed payments
        if ($admin->role === 'executive') {
            return $payment->status === 'completed';
        }
        
        return in_array($admin->role, ['admin', 'secretariat', 'finance']);
    }

    /**
     * Determine if the user can view all payments (including pending).
     */
    public function viewAll(Admin $admin): bool
    {
        return in_array($admin->role, ['admin', 'finance']);
    }

    /**
     * Determine if the user can manually mark payments as paid.
     */
    public function markAsPaid(Admin $admin): bool
    {
        return in_array($admin->role, ['admin', 'finance']);
    }
}

