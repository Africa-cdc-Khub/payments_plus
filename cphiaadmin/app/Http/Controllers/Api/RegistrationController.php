<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RegistrationController extends Controller
{
    /**
     * Get registrations data for DataTables.
     */
    public function index(Request $request)
    {
        $query = Registration::with(['user', 'package', 'participants']);

        // Apply filters
        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('nationality')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('country', $request->nationality);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('payment_reference', 'like', "%{$search}%");
            });
        }

        // Get paginated results
        $registrations = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json([
            'data' => $registrations->map(function($registration) {
                return [
                    'id' => $registration->id,
                    'user_name' => $registration->user ? ($registration->user->first_name . ' ' . $registration->user->last_name) : 'N/A',
                    'email' => $registration->user->email ?? 'N/A',
                    'registration_type' => ucfirst($registration->registration_type),
                    'package' => $registration->package->name ?? 'N/A',
                    'total_amount' => number_format($registration->total_amount, 2),
                    'currency' => $registration->currency,
                    'status' => $registration->status,
                    'payment_status' => $registration->payment_status,
                    'participants_count' => $registration->participants->count(),
                    'created_at' => $registration->created_at->format('M d, Y'),
                    'created_at_full' => $registration->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $registrations->total(),
            'per_page' => $registrations->perPage(),
            'current_page' => $registrations->currentPage(),
            'last_page' => $registrations->lastPage(),
        ]);
    }

    /**
     * Export registrations to CSV.
     */
    public function export(Request $request)
    {
        $query = Registration::with(['user', 'package', 'participants']);

        // Apply same filters as index
        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('nationality')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('country', $request->nationality);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $registrations = $query->latest()->get();

        $export = new class($registrations) implements FromCollection, WithHeadings {
            protected $registrations;

            public function __construct($registrations)
            {
                $this->registrations = $registrations;
            }

            public function collection()
            {
                return $this->registrations->map(function($registration) {
                    return [
                        'ID' => $registration->id,
                        'First Name' => $registration->user->first_name ?? 'N/A',
                        'Last Name' => $registration->user->last_name ?? 'N/A',
                        'Email' => $registration->user->email ?? 'N/A',
                        'Registration Type' => ucfirst($registration->registration_type),
                        'Package' => $registration->package->name ?? 'N/A',
                        'Amount' => $registration->total_amount,
                        'Currency' => $registration->currency,
                        'Status' => $registration->status,
                        'Payment Status' => $registration->payment_status,
                        'Participants' => $registration->participants->count(),
                        'Country' => $registration->user->country ?? 'N/A',
                        'Organization' => $registration->user->organization ?? 'N/A',
                        'Created At' => $registration->created_at->format('Y-m-d H:i:s'),
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'ID',
                    'First Name',
                    'Last Name',
                    'Email',
                    'Registration Type',
                    'Package',
                    'Amount',
                    'Currency',
                    'Status',
                    'Payment Status',
                    'Participants',
                    'Country',
                    'Organization',
                    'Created At',
                ];
            }
        };

        return Excel::download($export, 'registrations_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    /**
     * Get registration statistics.
     */
    public function stats()
    {
        return response()->json([
            'total' => Registration::count(),
            'individual' => Registration::where('registration_type', 'individual')->count(),
            'side_event' => Registration::where('registration_type', 'side_event')->count(),
            'exhibition' => Registration::where('registration_type', 'exhibition')->count(),
            'pending' => Registration::where('status', 'pending')->count(),
            'completed' => Registration::where('status', 'completed')->count(),
        ]);
    }
}
