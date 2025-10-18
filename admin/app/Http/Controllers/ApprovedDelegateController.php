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

        $query = Registration::with(['user', 'package', 'participants'])
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
                'Interpreter/Translator',
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

        // Handle sorting
        $sortField = $request->get('sort', 'travel_processed');
        $sortDirection = $request->get('direction', 'asc');
        
        switch ($sortField) {
            case 'name':
                $query->join('users', 'registrations.user_id', '=', 'users.id')
                      ->orderBy('users.first_name', $sortDirection)
                      ->orderBy('users.last_name', $sortDirection);
                break;
            case 'email':
                $query->join('users', 'registrations.user_id', '=', 'users.id')
                      ->orderBy('users.email', $sortDirection);
                break;
            case 'delegate_category':
                $query->join('users', 'registrations.user_id', '=', 'users.id')
                      ->orderBy('users.delegate_category', $sortDirection);
                break;
            case 'country':
                $query->join('users', 'registrations.user_id', '=', 'users.id')
                      ->orderBy('users.country', $sortDirection);
                break;
            case 'created_at':
                $query->orderBy('registrations.created_at', $sortDirection);
                break;
            case 'travel_status':
                $query->orderBy('travel_processed', $sortDirection);
                break;
            case 'travel_processed':
            default:
                $query->orderBy('travel_processed', $sortDirection)
                      ->orderBy('created_at', 'desc');
                break;
        }

        // Handle per page parameter
        $perPage = $request->get('per_page', 50);
        $perPage = min(max($perPage, 10), 200); // Min 10, Max 200
        
        $delegates = $query->paginate($perPage);

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

        $query = Registration::with(['user', 'package', 'participants'])
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
                'Interpreter/Translator',
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
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($delegates, $admin) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper accent display in Excel and other applications
            fwrite($file, "\xEF\xBB\xBF");
            
            $isTravels = $admin->role === 'travels';
            
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
                'ID',   
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
                ];
                
                if ($isTravels) {
                    $row[] = $safeValue($delegate->user->passport_number ?? '');
                    $row[] = $safeValue($delegate->user->airport_of_origin ?? '');
                    $row[] = $delegate->travel_processed ? 'Processed' : 'Pending';
                }
                
                $row = array_merge($row, [
                    $safeValue($delegate->user->dietary_requirements ?? ''),
                    $safeValue($delegate->user->special_needs ?? ''),
                    $delegate->user->requires_visa ? 'Yes' : 'No',
                    $delegate->created_at ? $delegate->created_at->format('Y-m-d H:i:s') : '',
                    $delegate->updated_at ? $delegate->updated_at->format('Y-m-d H:i:s') : '',
                ]);
                
                fputcsv($file, $row);
                
                // Include registration participants (group members) for approved delegates
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
                    ];
                    
                    if ($isTravels) {
                        $participantRow[] = $safeValue($participant->passport_number ?? '');
                        $participantRow[] = $safeValue($participant->airport_of_origin ?? '');
                        $participantRow[] = $delegate->travel_processed ? 'Processed' : 'Pending';
                    }
                    
                    $participantRow = array_merge($participantRow, [
                        $safeValue($participant->dietary_requirements ?? ''),
                        $safeValue($participant->special_needs ?? ''),
                        $participant->requires_visa ? 'Yes' : 'No',
                        $delegate->created_at ? $delegate->created_at->format('Y-m-d H:i:s') : '',
                        $delegate->updated_at ? $delegate->updated_at->format('Y-m-d H:i:s') : '',
                    ]);
                    
                    fputcsv($file, $participantRow);
                }
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
