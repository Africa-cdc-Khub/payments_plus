<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Jobs\SendInvitationJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendTestInvitation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invitation:test {registration_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test invitation to a specific registration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $registrationId = $this->argument('registration_id');
        
        if (!$registrationId) {
            // Find a paid or approved delegate registration
            $registration = Registration::with(['user', 'package'])
                ->where(function($query) {
                    $query->where('payment_status', 'paid')
                          ->orWhere(function($q) {
                              $q->where('package_id', config('app.delegate_package_id'))
                                ->where('status', 'approved');
                          });
                })
                ->first();
                
            if (!$registration) {
                $this->error('No eligible registrations found (paid or approved delegate)');
                return Command::FAILURE;
            }
            
            $registrationId = $registration->id;
        } else {
            $registration = Registration::with(['user', 'package'])->find($registrationId);
            
            if (!$registration) {
                $this->error("Registration #{$registrationId} not found");
                return Command::FAILURE;
            }
        }
        
        $this->info("Sending invitation for Registration #{$registration->id}");
        $this->info("User: {$registration->user->full_name} ({$registration->user->email})");
        $this->info("Package: {$registration->package->name}");
        $this->info("Status: {$registration->status}, Payment: {$registration->payment_status}");
        
        try {
            // Dispatch the job
            SendInvitationJob::dispatch($registration->id);
            $this->info("✓ Invitation job dispatched successfully!");
            $this->info("Processing job...");
            
            // Process the job immediately
            $this->call('queue:work', [
                '--once' => true,
                '--queue' => 'default',
            ]);
            
            $this->info("✓ Check the email inbox for {$registration->user->email}");
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("✗ Failed to send invitation: " . $e->getMessage());
            Log::error('Test invitation failed', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage()
            ]);
            return Command::FAILURE;
        }
    }
}
