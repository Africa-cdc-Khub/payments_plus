<?php

namespace App\Http\Controllers;

use App\Jobs\SendInvitationJob;
use App\Jobs\SendDelegateRejectionEmail;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DelegateController extends Controller
{
    /**
     * Display a listing of delegate registrations
     */
    public function index(Request $request)
    {
        $this->authorize('manageDelegates', Registration::class);
        
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
        $this->authorize('manageDelegates', Registration::class);
        
        // Verify this is a delegate registration
        if ($registration->package_id != config('app.delegate_package_id')) {
            return redirect()
                ->route('delegates.index')
                ->with('error', 'Invalid delegate registration.');
        }

        $registration->update(['status' => 'approved']);

        // Automatically send invitation email
        try {
            SendInvitationJob::dispatch($registration->id);
            Log::info("Invitation email queued for approved delegate registration #{$registration->id}");
            
            return redirect()
                ->route('delegates.index')
                ->with('success', "Delegate registration for {$registration->user->full_name} has been approved and invitation email has been queued.");
        } catch (\Exception $e) {
            Log::error("Failed to queue invitation email for delegate #{$registration->id}: " . $e->getMessage());
            
            return redirect()
                ->route('delegates.index')
                ->with('warning', "Delegate registration for {$registration->user->full_name} has been approved, but failed to queue invitation email. You can send it manually.");
        }
    }

    /**
     * Reject a delegate registration
     */
    public function reject(Request $request, Registration $registration)
    {
        $this->authorize('manageDelegates', Registration::class);
        
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

        // Automatically send rejection email
        try {
            SendDelegateRejectionEmail::dispatch($registration->id);
            Log::info("Rejection email queued for delegate registration #{$registration->id}");
            
            return redirect()
                ->route('delegates.index')
                ->with('success', "Delegate registration for {$registration->user->full_name} has been rejected and notification email has been queued.");
        } catch (\Exception $e) {
            Log::error("Failed to queue rejection email for delegate #{$registration->id}: " . $e->getMessage());
            
            return redirect()
                ->route('delegates.index')
                ->with('warning', "Delegate registration for {$registration->user->full_name} has been rejected, but failed to queue notification email.");
        }
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

