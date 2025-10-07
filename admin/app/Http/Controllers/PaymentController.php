<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Registration::with(['user', 'package'])
            ->where('payment_status', 'completed');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $payments = $query->latest('payment_completed_at')->paginate(20);

        return view('payments.index', compact('payments'));
    }

    public function show(Registration $payment)
    {
        $payment->load(['user', 'package']);
        
        return view('payments.show', compact('payment'));
    }
}

