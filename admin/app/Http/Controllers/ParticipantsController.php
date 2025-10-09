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
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Registration::class);

        // Get all packages for filter dropdown
        $packages = Package::orderBy('name')->get();

        // Base query for participants
        $query = Registration::with(['user', 'package'])
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
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('country')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('country', $request->country);
            });
        }

        // Get unique countries for filter dropdown
        $countries = Registration::with('user')
            ->where(function ($q) {
                $q->where('status', 'approved')
                  ->orWhere(function ($subQ) {
                      $subQ->where('payment_status', 'completed')
                           ->where('status', '!=', 'approved');
                  });
            })
            ->get()
            ->pluck('user.country')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Order by created date (newest first)
        $participants = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('participants.index', compact('participants', 'packages', 'countries'));
    }

    /**
     * Export participants to CSV
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', Registration::class);

        // Base query for participants
        $query = Registration::with(['user', 'package'])
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
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('country')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('country', $request->country);
            });
        }

        $participants = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'participants_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($participants) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            $headers = [
                'ID',
                'Full Name',
                'Email',
                'Phone',
                'Country',
                'Package',
                'Registration Date',
                'Type'
            ];
            
            // Add status columns for non-executive roles
            if (!in_array(auth('admin')->user()->role, ['executive'])) {
                array_splice($headers, 6, 0, ['Status', 'Payment Status']);
            }
            
            fputcsv($file, $headers);

            // CSV Data
            foreach ($participants as $participant) {
                $type = $participant->status === 'approved' ? 'Delegate' : 'Paid Participant';
                
                $row = [
                    $participant->id,
                    $participant->user->full_name ?? '',
                    $participant->user->email ?? '',
                    $participant->user->phone ?? '',
                    $participant->user->country ?? '',
                    $participant->package->name ?? '',
                    $participant->created_at ? $participant->created_at->format('Y-m-d H:i:s') : '',
                    $type
                ];
                
                // Add status columns for non-executive roles
                if (!in_array(auth('admin')->user()->role, ['executive'])) {
                    array_splice($row, 6, 0, [ucfirst($participant->status), ucfirst($participant->payment_status)]);
                }
                
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
