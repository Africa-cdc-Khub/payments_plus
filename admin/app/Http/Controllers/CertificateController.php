<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\RegistrationParticipant;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Display list of eligible participants for certificates
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Registration::class);

        $participants = $this->certificateService->getEligibleParticipants();

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = strtolower($request->search);
            $participants = $participants->filter(function($p) use ($search) {
                return str_contains(strtolower($p['name']), $search) 
                    || str_contains(strtolower($p['email']), $search)
                    || str_contains(strtolower((string)$p['registration_id']), $search);
            });
        }

        // Paginate
        $perPage = 50;
        $currentPage = $request->get('page', 1);
        $items = $participants->forPage($currentPage, $perPage);
        $total = $participants->count();

        return view('certificates.index', [
            'participants' => $items,
            'total' => $total,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'lastPage' => ceil($total / $perPage),
        ]);
    }

    /**
     * Preview certificate PDF
     */
    public function preview(Request $request)
    {
        $this->authorize('viewAny', Registration::class);

        $request->validate([
            'registration_id' => ['required', 'exists:registrations,id'],
            'participant_id' => ['sometimes', 'exists:registration_participants,id'],
        ]);

        try {
            $registration = Registration::with(['user', 'package'])->findOrFail($request->registration_id);

            // Verify eligibility
            $isDelegate = $registration->package_id == config('app.delegate_package_id');
            $canReceiveCertificate = $registration->payment_status === 'completed' 
                || ($isDelegate && $registration->status === 'approved');

            if (!$canReceiveCertificate) {
                return response()->json([
                    'error' => 'Only paid registrations or approved delegates can receive certificates.'
                ], 400);
            }

            $participant = null;
            if ($request->filled('participant_id')) {
                $participant = RegistrationParticipant::where('id', $request->participant_id)
                    ->where('registration_id', $registration->id)
                    ->firstOrFail();
            }

            return $this->certificateService->preview($registration, $participant);
        } catch (\Exception $e) {
            Log::error('Certificate Preview Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download certificate PDF
     */
    public function download(Request $request)
    {
        $this->authorize('viewAny', Registration::class);

        $request->validate([
            'registration_id' => ['required', 'exists:registrations,id'],
            'participant_id' => ['sometimes', 'exists:registration_participants,id'],
        ]);

        try {
            $registration = Registration::with(['user', 'package'])->findOrFail($request->registration_id);

            // Verify eligibility
            $isDelegate = $registration->package_id == config('app.delegate_package_id');
            $canReceiveCertificate = $registration->payment_status === 'completed' 
                || ($isDelegate && $registration->status === 'approved');

            if (!$canReceiveCertificate) {
                return redirect()->back()->with('error', 'Only paid registrations or approved delegates can download certificates.');
            }

            $participant = null;
            if ($request->filled('participant_id')) {
                $participant = RegistrationParticipant::where('id', $request->participant_id)
                    ->where('registration_id', $registration->id)
                    ->firstOrFail();
            }

            return $this->certificateService->download($registration, $participant);
        } catch (\Exception $e) {
            Log::error('Certificate Download Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate certificate PDF.');
        }
    }

    /**
     * Send certificate to individual participant
     */
    public function send(Request $request)
    {
        $this->authorize('viewAny', Registration::class);

        $request->validate([
            'registration_id' => ['required', 'exists:registrations,id'],
            'participant_id' => ['sometimes', 'nullable', 'exists:registration_participants,id'],
        ]);

        try {
            $registration = Registration::with(['user', 'package'])->findOrFail($request->registration_id);

            // Verify eligibility
            $isDelegate = $registration->package_id == config('app.delegate_package_id');
            $canReceiveCertificate = $registration->payment_status === 'completed' 
                || ($isDelegate && $registration->status === 'approved');

            if (!$canReceiveCertificate) {
                return redirect()->back()->with('error', 'Only paid registrations or approved delegates can receive certificates.');
            }

            $participant = null;
            if ($request->filled('participant_id')) {
                $participant = RegistrationParticipant::where('id', $request->participant_id)
                    ->where('registration_id', $registration->id)
                    ->firstOrFail();
            }

            $result = $this->certificateService->sendCertificate($registration, $participant);

            if ($result) {
                $participantName = $participant 
                    ? $participant->first_name . ' ' . $participant->last_name
                    : $registration->user->first_name . ' ' . $registration->user->last_name;
                return redirect()->back()->with('success', "Certificate queued for sending to {$participantName}. Email will be sent in the background.");
            } else {
                return redirect()->back()->with('error', 'Failed to queue certificate for sending.');
            }
        } catch (\Exception $e) {
            Log::error('Certificate Send Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to send certificate: ' . $e->getMessage());
        }
    }

    /**
     * Send bulk certificates (selected participants)
     */
    public function sendBulk(Request $request)
    {
        $this->authorize('viewAny', Registration::class);

        $request->validate([
            'participants' => ['required', 'array'],
            'participants.*.registration_id' => ['required', 'exists:registrations,id'],
            'participants.*.participant_id' => ['sometimes', 'nullable', 'exists:registration_participants,id'],
        ]);

        $results = $this->certificateService->sendBulkCertificates($request->participants);

        if ($results['queued'] > 0) {
            $message = "Successfully queued {$results['queued']} certificate(s) for sending.";
            if ($results['failed'] > 0) {
                $message .= " {$results['failed']} could not be queued.";
            }
            $message .= " Emails will be sent in the background.";
            return redirect()->back()->with('success', $message);
        } else {
            return redirect()->back()->with('error', 'Failed to queue certificates. ' . implode(', ', $results['errors']));
        }
    }

    /**
     * Send certificates to all eligible participants
     */
    public function sendAll(Request $request)
    {
        $this->authorize('viewAny', Registration::class);

        // Get all eligible participants (not paginated)
        $allParticipants = $this->certificateService->getEligibleParticipants();

        // Convert to the format expected by sendBulkCertificates
        $participantsData = $allParticipants->map(function($participant) {
            return [
                'registration_id' => $participant['registration_id'],
                'participant_id' => $participant['participant_id'],
            ];
        })->toArray();

        $results = $this->certificateService->sendBulkCertificates($participantsData);

        if ($results['queued'] > 0) {
            $message = "Successfully queued {$results['queued']} certificate(s) for sending to all eligible participants.";
            if ($results['failed'] > 0) {
                $message .= " {$results['failed']} could not be queued.";
            }
            $message .= " Emails will be sent in the background.";
            return redirect()->back()->with('success', $message);
        } else {
            return redirect()->back()->with('error', 'Failed to queue certificates. ' . implode(', ', $results['errors']));
        }
    }
}
