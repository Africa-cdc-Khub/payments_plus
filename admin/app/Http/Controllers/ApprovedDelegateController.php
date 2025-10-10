<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Jobs\SendPassportRequestJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ApprovedDelegateController extends Controller
{
    public function index(Request $request)
    {
        // Only admin, secretariat, executive, and travels can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || !in_array($admin->role, ['admin', 'secretariat', 'executive', 'travels'])) {
            abort(403, 'Unauthorized access.');
        }

        $query = Registration::with(['user', 'package'])
            ->where('package_id', config('app.delegate_package_id'))
            ->where('status', 'approved');

        // Travels role can only see fully sponsored delegates
        if ($admin->role === 'travels') {
            $fullySponsoredCategories = [
                'Oral abstract presenter',
                'Invited speaker/Moderator',
                'Scientific Program Committee Member',
                'Secretariat',
                'Media Partner',
                'Youth Program Participant',
                'Interpreter/Translators'
            ];
            
            $query->whereHas('user', function($q) use ($fullySponsoredCategories) {
                $q->whereIn('delegate_category', $fullySponsoredCategories);
            });
        }

        // Filter by delegate category
        if ($request->filled('delegate_category')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('delegate_category', $request->delegate_category);
            });
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('country', $request->country);
            });
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by travel processed status
        if ($request->filled('travel_processed')) {
            $query->where('travel_processed', $request->travel_processed);
        }

        // Order by unprocessed first, then by creation date
        $delegates = $query->orderBy('travel_processed', 'asc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        // Get unique delegate categories for filter - from all users with delegate category
        $delegateCategories = \App\Models\User::whereNotNull('delegate_category')
            ->where('delegate_category', '!=', '')
            ->distinct()
            ->orderBy('delegate_category')
            ->pluck('delegate_category');

        // Get unique countries for filter - from all users with country
        $countries = \App\Models\User::whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        return view('approved-delegates.index', compact('delegates', 'delegateCategories', 'countries'));
    }

    public function export(Request $request)
    {
        // Only admin, secretariat, executive, and travels can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || !in_array($admin->role, ['admin', 'secretariat', 'executive', 'travels'])) {
            abort(403, 'Unauthorized access.');
        }

        $query = Registration::with(['user', 'package'])
            ->where('package_id', config('app.delegate_package_id'))
            ->where('status', 'approved');

        // Travels role can only see fully sponsored delegates
        if ($admin->role === 'travels') {
            $fullySponsoredCategories = [
                'Oral abstract presenter',
                'Invited speaker/Moderator',
                'Scientific Program Committee Member',
                'Secretariat',
                'Media Partner',
                'Youth Program Participant',
                'Interpreter/Translators'
            ];
            
            $query->whereHas('user', function($q) use ($fullySponsoredCategories) {
                $q->whereIn('delegate_category', $fullySponsoredCategories);
            });
        }

        // Apply same filters as index
        if ($request->filled('delegate_category')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('delegate_category', $request->delegate_category);
            });
        }

        if ($request->filled('country')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('country', $request->country);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by travel processed status
        if ($request->filled('travel_processed')) {
            $query->where('travel_processed', $request->travel_processed);
        }

        // Order by unprocessed first, then by creation date
        $delegates = $query->orderBy('travel_processed', 'asc')
                          ->orderBy('created_at', 'desc')
                          ->get();

        // Generate CSV
        $filename = 'approved_delegates_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($delegates, $admin) {
            $file = fopen('php://output', 'w');
            
            $isTravels = $admin->role === 'travels';
            
            // CSV Headers
            $headers = [
                'ID',
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Title',
                'Organization',
                'Position',
                'Country',
                'City',
                'Delegate Category',
            ];
            
            if ($isTravels) {
                $headers[] = 'Passport Number';
                $headers[] = 'Airport of Origin';
                $headers[] = 'Travel Status';
            }
            
            $headers = array_merge($headers, [
                'Dietary Requirements',
                'Special Needs',
                'Requires Visa',
                'Registration Date',
                'Approval Date',
            ]);
            
            fputcsv($file, $headers);

            // CSV Data
            foreach ($delegates as $delegate) {
                $row = [
                    $delegate->id,
                    $delegate->user->first_name ?? '',
                    $delegate->user->last_name ?? '',
                    $delegate->user->email ?? '',
                    $delegate->user->phone ?? '',
                    $delegate->user->title ?? '',
                    $delegate->user->organization ?? '',
                    $delegate->user->position ?? '',
                    $delegate->user->country ?? '',
                    $delegate->user->city ?? '',
                    $delegate->user->delegate_category ?? '',
                ];
                
                if ($isTravels) {
                    $row[] = $delegate->user->passport_number ?? '';
                    $row[] = $delegate->user->airport_of_origin ?? '';
                    $row[] = $delegate->travel_processed ? 'Processed' : 'Pending';
                }
                
                $row = array_merge($row, [
                    $delegate->user->dietary_requirements ?? '',
                    $delegate->user->special_needs ?? '',
                    $delegate->user->requires_visa ? 'Yes' : 'No',
                    $delegate->created_at ? $delegate->created_at->format('Y-m-d H:i:s') : '',
                    $delegate->updated_at ? $delegate->updated_at->format('Y-m-d H:i:s') : '',
                ]);
                
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function markAsProcessed(Request $request, Registration $registration)
    {
        // Only travels role can mark as processed
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'travels') {
            abort(403, 'Unauthorized access.');
        }

        // Validate the registration is an approved delegate
        if ($registration->package_id != config('app.delegate_package_id') || $registration->status !== 'approved') {
            return redirect()->back()->with('error', 'Invalid registration.');
        }

        // Toggle the travel_processed status
        $registration->update([
            'travel_processed' => !$registration->travel_processed
        ]);

        $status = $registration->travel_processed ? 'processed' : 'unprocessed';
        
        return redirect()->back()->with('success', "Delegate marked as {$status}.");
    }

    public function requestPassport(Request $request, Registration $registration)
    {
        // Only admin and travels roles can request passport
        $admin = Auth::guard('admin')->user();
        if (!$admin || !in_array($admin->role, ['admin', 'travels'])) {
            abort(403, 'Unauthorized access.');
        }

        // Validate the registration is an approved delegate
        if ($registration->package_id != config('app.delegate_package_id') || $registration->status !== 'approved') {
            return redirect()->back()->with('error', 'Invalid registration.');
        }

        try {
            // Dispatch passport request job
            SendPassportRequestJob::dispatch($registration->id);

            return redirect()->back()->with('success', 'Passport request email has been queued for ' . $registration->user->full_name . '.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to queue passport request email: ' . $e->getMessage());
        }
    }
}
