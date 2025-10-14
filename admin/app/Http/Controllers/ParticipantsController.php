<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ParticipantsController extends Controller
{
    /**
     * Display a listing of all participants (approved delegates + paid registrations)
     * Includes both primary registrants and additional group members
     */
    public function index(Request $request)
    {
     
        // Get all packages for filter dropdown
        $packages = Package::orderBy('name')->get();

        // Base query for registrations
        $query = Registration::with(['user', 'package', 'participants'])
            ->where(function ($q) {
                $q->where('status', 'approved') // Approved delegates
                  ->orWhere(function ($subQ) {
                      $subQ->where('payment_status', 'completed')
                           ->where('status', '!=', 'approved'); // Paid registrations that are not delegates
                  });
            });

        // Apply filters
        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function ($subQ) use ($search) {
                    $subQ->where('full_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('participants', function($subQ) use ($search) {
                    $subQ->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('country')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('user', function ($subQ) use ($request) {
                    $subQ->where('country', $request->country);
                })
                ->orWhereHas('participants', function($subQ) use ($request) {
                    $subQ->where('nationality', $request->country);
                });
            });
        }

        // Get unique countries for filter dropdown (from both users and participants)
        $userCountries = Registration::with('user')
            ->where(function ($q) {
                $q->where('status', 'approved')
                  ->orWhere(function ($subQ) {
                      $subQ->where('payment_status', 'completed')
                           ->where('status', '!=', 'approved');
                  });
            })
            ->get()
            ->pluck('user.country')
            ->filter();
        
        $participantCountries = \App\Models\RegistrationParticipant::whereHas('registration', function($q) {
                $q->where(function ($subQ) {
                    $subQ->where('status', 'approved')
                      ->orWhere(function ($sub2Q) {
                          $sub2Q->where('payment_status', 'completed')
                               ->where('status', '!=', 'approved');
                      });
                });
            })
            ->get()
            ->pluck('nationality')
            ->filter();
        
        $countries = $userCountries->merge($participantCountries)
            ->unique()
            ->sort()
            ->values();

        // Order by created date (newest first)
        $registrations = $query->orderBy('created_at', 'desc')->paginate(50);

        // Calculate total participant count (registrants + group members)
        $totalParticipants = $registrations->sum(function($registration) {
            return 1 + $registration->participants->count();
        });

        return view('participants.index', compact('registrations', 'packages', 'countries', 'totalParticipants'));
    }

    /**
     * Export participants to CSV (includes both registrants and group members)
     */
    public function export(Request $request)
    {

        // Base query for registrations
        $query = Registration::with(['user', 'package', 'participants'])
            ->where(function ($q) {
                $q->where('status', 'approved') // Approved delegates
                  ->orWhere(function ($subQ) {
                      $subQ->where('payment_status', 'completed')
                           ->where('status', '!=', 'approved'); // Paid registrations that are not delegates
                  });
            });

        // Apply same filters as index
        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function ($subQ) use ($search) {
                    $subQ->where('full_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('participants', function($subQ) use ($search) {
                    $subQ->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('country')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('user', function ($subQ) use ($request) {
                    $subQ->where('country', $request->country);
                })
                ->orWhereHas('participants', function($subQ) use ($request) {
                    $subQ->where('nationality', $request->country);
                });
            });
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'participants_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($registrations) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            $csvHeaders = [
                'Registration ID',
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Country/Nationality',
                'Package',
                'Delegate Category',
                'Registration Date',
                'Participant Type',
                'Member Type'
            ];
            
            // Add status columns for non-executive roles
            if (!in_array(auth('admin')->user()->role, ['executive'])) {
                array_splice($csvHeaders, 7, 0, ['Status', 'Payment Status']);
            }
            
            fputcsv($file, $csvHeaders);

            // CSV Data - Include registrants and their group members
            foreach ($registrations as $registration) {
                $type = $registration->status === 'approved' ? 'Delegate' : 'Paid Participant';
                
                // Primary Registrant Row
                $row = [
                    $registration->id,
                    $registration->user->first_name ?? '',
                    $registration->user->last_name ?? '',
                    $registration->user->email ?? '',
                    $registration->user->phone ?? '',
                    $registration->user->country ?? '',
                    $registration->package->name ?? '',
                    $registration->user->delegate_category ?? '',
                    $registration->created_at ? $registration->created_at->format('Y-m-d H:i:s') : '',
                    $type,
                    'Primary Registrant'
                ];
                
                // Add status columns for non-executive roles
                if (!in_array(auth('admin')->user()->role, ['executive'])) {
                    array_splice($row, 7, 0, [ucfirst($registration->status), ucfirst($registration->payment_status)]);
                }
                
                fputcsv($file, $row);
                
                // Additional Group Members
                foreach ($registration->participants as $groupMember) {
                    $memberRow = [
                        $registration->id,
                        $groupMember->first_name ?? '',
                        $groupMember->last_name ?? '',
                        $groupMember->email ?? '',
                        '',
                        $groupMember->nationality ?? '',
                        $registration->package->name ?? '',
                        $groupMember->delegate_category ?? '',
                        $registration->created_at ? $registration->created_at->format('Y-m-d H:i:s') : '',
                        'Group Member',
                        'Additional Member'
                    ];
                    
                    // Add status columns for non-executive roles
                    if (!in_array(auth('admin')->user()->role, ['executive'])) {
                        array_splice($memberRow, 7, 0, ['Group Member', ucfirst($registration->payment_status)]);
                    }
                    
                    fputcsv($file, $memberRow);
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
