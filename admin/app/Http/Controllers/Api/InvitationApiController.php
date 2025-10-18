<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendInvitationJob;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvitationApiController extends Controller
{
    /**
     * Send invitation email for a registration
     * Only accessible from localhost
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        // Check if request is from localhost
        $allowedHosts = ['127.0.0.1', 'localhost', '::1'];
        $requestIp = $request->ip();
        
        if (!in_array($requestIp, $allowedHosts)) {
            Log::warning("Invitation API access denied from IP: {$requestIp}");
            return response()->json([
                'success' => false,
                'error' => 'Access denied. This endpoint is only accessible from localhost.'
            ], 403);
        }

        // Validate request
        $validated = $request->validate([
            'registration_id' => ['required', 'integer', 'exists:registrations,id']
        ]);

        try {
            // Load registration with relationships
            $registration = Registration::with(['user', 'package'])
                ->findOrFail($validated['registration_id']);

            // Check if registration is eligible for invitation
            $isDelegate = $registration->package_id == config('app.delegate_package_id');
            $canReceiveInvitation = $registration->isPaid() || ($isDelegate && $registration->status === 'approved');

            if (!$canReceiveInvitation) {
                return response()->json([
                    'success' => false,
                    'error' => 'Registration is neither paid nor an approved delegate',
                    'registration_id' => $registration->id,
                    'payment_status' => $registration->payment_status,
                    'status' => $registration->status
                ], 400);
            }

            // Dispatch the invitation job
            SendInvitationJob::dispatch($registration->id);

            Log::info("Invitation email triggered via API for registration #{$registration->id}");

            return response()->json([
                'success' => true,
                'message' => 'Invitation email queued successfully',
                'registration_id' => $registration->id,
                'user_email' => $registration->user->email,
                'user_name' => $registration->user->full_name,
                'package' => $registration->package->name,
                'queued_at' => now()->toDateTimeString()
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Registration not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error("Failed to queue invitation email via API: " . $e->getMessage(), [
                'registration_id' => $validated['registration_id'] ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to queue invitation email',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
