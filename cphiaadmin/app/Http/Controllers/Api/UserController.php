<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserController extends Controller
{
    /**
     * Get users data for DataTables.
     */
    public function index(Request $request)
    {
        $query = User::with(['registrations']);

        // Apply filters
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('requires_visa')) {
            $query->where('requires_visa', $request->requires_visa === 'true');
        }

        if ($request->filled('attendance_status')) {
            $query->where('attendance_status', $request->attendance_status);
        }

        if ($request->filled('delegate_category')) {
            $query->where('delegate_category', $request->delegate_category);
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
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('organization', 'like', "%{$search}%")
                  ->orWhere('passport_number', 'like', "%{$search}%");
            });
        }

        // Get paginated results
        $users = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json([
            'data' => $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'country' => $user->country ?? 'N/A',
                    'organization' => $user->organization ?? 'N/A',
                    'delegate_category' => $user->delegate_category ?? 'N/A',
                    'requires_visa' => $user->requires_visa ? 'Yes' : 'No',
                    'passport_number' => $user->passport_number ?? 'N/A',
                    'attendance_status' => $user->attendance_status ?? 'pending',
                    'registrations_count' => $user->registrations->count(),
                    'created_at' => $user->created_at->format('M d, Y'),
                ];
            }),
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
        ]);
    }

    /**
     * Export users to CSV.
     */
    public function export(Request $request)
    {
        $query = User::with(['registrations']);

        // Apply same filters as index
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('requires_visa')) {
            $query->where('requires_visa', $request->requires_visa === 'true');
        }

        if ($request->filled('attendance_status')) {
            $query->where('attendance_status', $request->attendance_status);
        }

        if ($request->filled('delegate_category')) {
            $query->where('delegate_category', $request->delegate_category);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->latest()->get();

        $export = new class($users) implements FromCollection, WithHeadings {
            protected $users;

            public function __construct($users)
            {
                $this->users = $users;
            }

            public function collection()
            {
                return $this->users->map(function($user) {
                    return [
                        'ID' => $user->id,
                        'Title' => $user->title ?? '',
                        'First Name' => $user->first_name,
                        'Last Name' => $user->last_name,
                        'Email' => $user->email,
                        'Phone' => $user->phone ?? 'N/A',
                        'Nationality' => $user->nationality ?? 'N/A',
                        'Country' => $user->country ?? 'N/A',
                        'Organization' => $user->organization ?? 'N/A',
                        'Position' => $user->position ?? 'N/A',
                        'Delegate Category' => $user->delegate_category ?? 'N/A',
                        'Requires Visa' => $user->requires_visa ? 'Yes' : 'No',
                        'Passport Number' => $user->passport_number ?? 'N/A',
                        'Attendance Status' => $user->attendance_status ?? 'pending',
                        'Address' => $user->address_line1 ?? 'N/A',
                        'City' => $user->city ?? 'N/A',
                        'State' => $user->state ?? 'N/A',
                        'Postal Code' => $user->postal_code ?? 'N/A',
                        'Created At' => $user->created_at->format('Y-m-d H:i:s'),
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'ID',
                    'Title',
                    'First Name',
                    'Last Name',
                    'Email',
                    'Phone',
                    'Nationality',
                    'Country',
                    'Organization',
                    'Position',
                    'Delegate Category',
                    'Requires Visa',
                    'Passport Number',
                    'Attendance Status',
                    'Address',
                    'City',
                    'State',
                    'Postal Code',
                    'Created At',
                ];
            }
        };

        return Excel::download($export, 'participants_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    /**
     * Update attendance status.
     */
    public function updateAttendance(Request $request, $id)
    {
        $request->validate([
            'attendance_status' => 'required|in:pending,present,absent',
        ]);

        $user = User::findOrFail($id);
        $oldStatus = $user->attendance_status;

        $user->update([
            'attendance_status' => $request->attendance_status,
            'attendance_verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Attendance status updated from {$oldStatus} to {$request->attendance_status}",
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Get user statistics.
     */
    public function stats()
    {
        $africanCountries = $this->getAfricanCountries();

        return response()->json([
            'total' => User::count(),
            'african' => User::whereIn('country', $africanCountries)->count(),
            'non_african' => User::whereNotIn('country', $africanCountries)->count(),
            'visa_required' => User::where('requires_visa', true)->count(),
            'present' => User::where('attendance_status', 'present')->count(),
            'absent' => User::where('attendance_status', 'absent')->count(),
            'pending' => User::where('attendance_status', 'pending')->count(),
        ]);
    }

    private function getAfricanCountries()
    {
        return [
            'Algeria', 'Angola', 'Benin', 'Botswana', 'Burkina Faso', 'Burundi',
            'Cameroon', 'Cape Verde', 'Central African Republic', 'Chad', 'Comoros',
            'Congo', 'Democratic Republic of Congo', 'Djibouti', 'Egypt',
            'Equatorial Guinea', 'Eritrea', 'Eswatini', 'Ethiopia', 'Gabon',
            'Gambia', 'Ghana', 'Guinea', 'Guinea-Bissau', 'Ivory Coast', 'Kenya',
            'Lesotho', 'Liberia', 'Libya', 'Madagascar', 'Malawi', 'Mali',
            'Mauritania', 'Mauritius', 'Morocco', 'Mozambique', 'Namibia', 'Niger',
            'Nigeria', 'Rwanda', 'Sao Tome and Principe', 'Senegal', 'Seychelles',
            'Sierra Leone', 'Somalia', 'South Africa', 'South Sudan', 'Sudan',
            'Tanzania', 'Togo', 'Tunisia', 'Uganda', 'Zambia', 'Zimbabwe'
        ];
    }
}
