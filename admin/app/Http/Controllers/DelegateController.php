<?php

namespace App\Http\Controllers;

use App\Jobs\SendInvitationJob;
use App\Jobs\SendDelegateRejectionEmail;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

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

        // Filter by delegate category
        if ($request->filled('delegate_category')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('delegate_category', $request->delegate_category);
            });
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('country', $request->country);
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

        // Get unique delegate categories for filter
        $delegateCategories = User::whereNotNull('delegate_category')
            ->where('delegate_category', '!=', '')
            ->distinct()
            ->orderBy('delegate_category')
            ->pluck('delegate_category');

        // Get unique countries for filter
        $countries = User::whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        return view('delegates.index', compact('delegates', 'statusCounts', 'delegateCategories', 'countries'));
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
     * Reset delegate status back to pending (cancel approval or recall rejection)
     */
    public function resetToPending(Registration $registration)
    {
        $this->authorize('manageDelegates', Registration::class);
        
        // Admin only for this action
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access. Only admins can reset delegate status.');
        }
        
        // Verify this is a delegate registration
        if ($registration->package_id != config('app.delegate_package_id')) {
            return redirect()
                ->route('delegates.index')
                ->with('error', 'Invalid delegate registration.');
        }

        // Can only reset if currently approved or rejected
        if (!in_array($registration->status, ['approved', 'rejected'])) {
            return redirect()
                ->route('delegates.index')
                ->with('error', 'Can only reset delegates that are approved or rejected.');
        }

        $previousStatus = $registration->status;
        
        $registration->update([
            'status' => 'pending',
            'rejection_reason' => null // Clear rejection reason if any
        ]);

        Log::info("Delegate registration #{$registration->id} reset from {$previousStatus} to pending by admin #{$admin->id}", [
            'admin' => $admin->username,
            'delegate' => $registration->user->full_name,
        ]);

        $action = $previousStatus === 'approved' ? 'Approval cancelled' : 'Rejection recalled';
        
        return redirect()
            ->route('delegates.index')
            ->with('success', "{$action} for {$registration->user->full_name}. Delegate is now pending review.");
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

    /**
     * Export delegates to CSV
     */
    public function export(Request $request)
    {
        $this->authorize('manageDelegates', Registration::class);
        
        $delegatePackageId = config('app.delegate_package_id');
        
        $query = Registration::with(['user', 'package', 'participants'])
            ->where('package_id', $delegatePackageId);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('delegate_category')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('delegate_category', $request->delegate_category);
            });
        }

        if ($request->filled('country')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('country', $request->country);
            });
        }

        // Order by status priority, then by creation date
        $delegates = $query
            ->orderByRaw("CASE 
                WHEN status = 'pending' THEN 1 
                WHEN status = 'approved' THEN 2 
                WHEN status = 'rejected' THEN 3 
                ELSE 4 END")
            ->latest('created_at')
            ->get();

        // Generate CSV
        $filename = 'delegates_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($delegates) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper accent display in Excel and other applications
            fwrite($file, "\xEF\xBB\xBF");
            
            // Helper function: ensure value is UTF-8 and safe for Excel (handles accents/diacritics correctly)
            $safeValue = function($value) {
                if (is_null($value)) return '';
                // If already UTF-8, keep as-is, but ensure any improper bytes fixed
                $str = (string)$value;
                if (!mb_detect_encoding($str, 'UTF-8', true)) {
                    $str = mb_convert_encoding($str, 'UTF-8');
                }
                // Some Office/Excel versions may break on long accented chars if not normalized:
                return normalizer_is_normalized($str, \Normalizer::FORM_C) ? $str : normalizer_normalize($str, \Normalizer::FORM_C);
            };
            
            // CSV Headers
            $headers = [
                'REG ID',
                'Title',
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Nationality',
                'Organization',
                'Position',
                'Country',
                'City',
                'Delegate Category',
                'Status',
                'Registration Date',
                'Updated Date',
                'Rejection Reason'
            ];
            
            fputcsv($file, $headers);

            // CSV Data
            foreach ($delegates as $delegate) {
                $row = [
                    $delegate->id,
                    $safeValue($delegate->user->title ?? ''),
                    $safeValue($delegate->user->first_name ?? ''),
                    $safeValue($delegate->user->last_name ?? ''),
                    $safeValue($delegate->user->email ?? ''),
                    $safeValue($delegate->user->phone ?? ''),
                    $safeValue($delegate->user->nationality ?? ''),
                    $safeValue($delegate->user->organization ?? ''),
                    $safeValue($delegate->user->position ?? ''),
                    $safeValue($delegate->user->country ?? ''),
                    $safeValue($delegate->user->city ?? ''),
                    $safeValue($delegate->user->delegate_category ?? ''),
                    ucfirst($delegate->status),
                    $delegate->created_at ? $delegate->created_at->format('Y-m-d H:i:s') : '',
                    $delegate->updated_at ? $delegate->updated_at->format('Y-m-d H:i:s') : '',
                    $safeValue($delegate->rejection_reason ?? ''),
                ];
                
                fputcsv($file, $row);
                
                // Include registration participants (group members) for delegates
                foreach ($delegate->participants as $participant) {
                    $participantRow = [
                        $delegate->id . ' (Group Member)',
                        $safeValue($participant->title ?? ''),
                        $safeValue($participant->first_name ?? ''),
                        $safeValue($participant->last_name ?? ''),
                        $safeValue($participant->email ?? ''),
                        $safeValue($participant->phone ?? ''),
                        $safeValue($participant->nationality ?? ''),
                        $safeValue($participant->organization ?? ''),
                        $safeValue($participant->position ?? ''),
                        $safeValue($participant->country ?? ''),
                        $safeValue($participant->city ?? ''),
                        $safeValue($participant->delegate_category ?? ''),
                        ucfirst($delegate->status),
                        $delegate->created_at ? $delegate->created_at->format('Y-m-d H:i:s') : '',
                        $delegate->updated_at ? $delegate->updated_at->format('Y-m-d H:i:s') : '',
                        $safeValue($delegate->rejection_reason ?? ''),
                    ];
                    
                    fputcsv($file, $participantRow);
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}

