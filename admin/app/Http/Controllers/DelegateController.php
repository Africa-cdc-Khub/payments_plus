<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DelegateController extends Controller
{
    /**
     * Display a listing of delegate registrations
     */
    public function index(Request $request)
    {
        $delegatePackageId = config('app.delegate_package_id');
        
        $query = Registration::with(['user', 'package'])
            ->where('package_id', $delegatePackageId);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by user
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $delegates = $query
            ->orderByRaw("CASE 
                WHEN status = 'pending' THEN 1 
                WHEN status = 'approved' THEN 2 
                WHEN status = 'rejected' THEN 3 
                ELSE 4 END")
            ->latest('created_at')
            ->paginate(20);

        $statusCounts = Registration::where('package_id', $delegatePackageId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('delegates.index', compact('delegates', 'statusCounts'));
    }

    /**
     * Approve a delegate registration
     */
    public function approve(Registration $registration)
    {
        // Verify this is a delegate registration
        if ($registration->package_id != config('app.delegate_package_id')) {
            return redirect()
                ->route('delegates.index')
                ->with('error', 'Invalid delegate registration.');
        }

        $registration->update(['status' => 'approved']);

        return redirect()
            ->route('delegates.index')
            ->with('success', "Delegate registration for {$registration->user->full_name} has been approved.");
    }

    /**
     * Reject a delegate registration
     */
    public function reject(Request $request, Registration $registration)
    {
        // Verify this is a delegate registration
        if ($registration->package_id != config('app.delegate_package_id')) {
            return redirect()
                ->route('delegates.index')
                ->with('error', 'Invalid delegate registration.');
        }

        $registration->update([
            'status' => 'rejected',
            'rejection_reason' => $request->input('reason')
        ]);

        return redirect()
            ->route('delegates.index')
            ->with('success', "Delegate registration for {$registration->user->full_name} has been rejected.");
    }

    /**
     * Show delegate details
     */
    public function show(Registration $registration)
    {
        // Verify this is a delegate registration
        if ($registration->package_id != config('app.delegate_package_id')) {
            return redirect()
                ->route('delegates.index')
                ->with('error', 'Invalid delegate registration.');
        }

        $registration->load(['user', 'package', 'participants']);
        
        return view('delegates.show', compact('registration'));
    }
}

