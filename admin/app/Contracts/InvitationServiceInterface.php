<?php

namespace App\Contracts;

use App\Models\Registration;

interface InvitationServiceInterface
{
    public function generatePDF(Registration $registration): string;
    
    public function sendInvitation(Registration $registration): bool;
    
    public function sendBulkInvitations(array $registrationIds): array;
    
    public function previewInvitation(Registration $registration): string;
}

