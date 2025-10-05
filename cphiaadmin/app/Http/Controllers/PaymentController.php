<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display the specified payment.
     */
    public function show($id)
    {
        $payment = Payment::with([
            'registration.user',
            'registration.package',
            'registration.participants'
        ])->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }
}
