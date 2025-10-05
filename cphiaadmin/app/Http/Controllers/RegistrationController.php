<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    /**
     * Display the specified registration.
     */
    public function show($id)
    {
        $registration = Registration::with([
            'user',
            'package',
            'participants',
            'payments'
        ])->findOrFail($id);

        return view('admin.registrations.show', compact('registration'));
    }
}
