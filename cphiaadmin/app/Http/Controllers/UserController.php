<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = User::with([
            'registrations.package',
            'registrations.payments'
        ])->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }
}
