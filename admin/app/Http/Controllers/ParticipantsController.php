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

        // Handle sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
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
            case 'package':
                $query->join('packages', 'registrations.package_id', '=', 'packages.id')
                      ->orderBy('packages.name', $sortDirection);
                break;
            case 'status':
                $query->orderBy('status', $sortDirection);
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }

        // Handle per page parameter
        $perPage = $request->get('per_page', 50);
        $perPage = min(max($perPage, 10), 200); // Min 10, Max 200
        
        $registrations = $query->paginate($perPage);

        // Calculate total participant count (registrants + group members)
        $totalParticipants = $registrations->sum(function($registration) {
            return 1 + $registration->participants->count();
        });

        return view('participants.index', compact('registrations', 'packages', 'countries', 'totalParticipants'));
    }

    /**
     * Export participants to CSV (includes both registrants and group members)
     */
    /**
     * Export participants to CSV (handles Latin, French, and Spanish names with accents)
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

        // Generate CSV with UTF-8 BOM for proper encoding (required for accented names!)
        $filename = 'participants_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($registrations) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel/Windows compatibility (so accented chars are not corrupted)
            fwrite($file, "\xEF\xBB\xBF");

            // CSV Headers
            $csvHeaders = [
                'Registration ID',
                'Title',
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

            foreach ($registrations as $registration) {
                $type = $registration->status === 'approved' ? 'Delegate' : 'Paid Participant';

                // Primary Registrant Row
                $row = [
                    $registration->id,
                    $safeValue($registration->user->title ?? ''),
                    $safeValue($registration->user->first_name ?? ''),
                    $safeValue($registration->user->last_name ?? ''),
                    $safeValue($registration->user->email ?? ''),
                    $safeValue($registration->user->phone ?? ''),
                    $safeValue($registration->user->country ?? ''),
                    $safeValue($registration->package->name ?? ''),
                    $safeValue($registration->user->delegate_category ?? ''),
                    $registration->created_at ? $registration->created_at->format('Y-m-d H:i:s') : '',
                    $type,
                    'Primary Registrant'
                ];

                // Add status columns for non-executive roles
                if (!in_array(auth('admin')->user()->role, ['executive'])) {
                    array_splice($row, 7, 0, [
                        $safeValue(ucfirst($registration->status)),
                        $safeValue(ucfirst($registration->payment_status))
                    ]);
                }

                fputcsv($file, $row);

                foreach ($registration->participants as $groupMember) {
                    $memberRow = [
                        $registration->id,
                        $safeValue($groupMember->title ?? ''),
                        $safeValue($groupMember->first_name ?? ''),
                        $safeValue($groupMember->last_name ?? ''),
                        $safeValue($groupMember->email ?? ''),
                        '',
                        $safeValue($groupMember->nationality ?? ''),
                        $safeValue($registration->package->name ?? ''),
                        $safeValue($groupMember->delegate_category ?? ''),
                        $registration->created_at ? $registration->created_at->format('Y-m-d H:i:s') : '',
                        'Group Member',
                        'Additional Member'
                    ];

                    // Add status columns for non-executive roles
                    if (!in_array(auth('admin')->user()->role, ['executive'])) {
                        array_splice($memberRow, 7, 0, [
                            'Group Member',
                            $safeValue(ucfirst($registration->payment_status))
                        ]);
                    }

                    fputcsv($file, $memberRow);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
