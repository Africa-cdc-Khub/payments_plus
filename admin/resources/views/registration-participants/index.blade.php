@extends('layouts.app')

@section('title', 'Group Participants')
@section('page-title', 'Group Participants - ' . $registration->user->full_name)

@section('content')
<!-- Registration Summary -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <p class="text-sm text-gray-500">Registrant</p>
                <p class="text-lg font-semibold">{{ $registration->user->full_name }}</p>
                <p class="text-sm text-gray-600">{{ $registration->user->email }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Package</p>
                <p class="text-lg font-semibold">{{ $registration->package->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Registration Type</p>
                <p class="text-lg font-semibold">{{ ucfirst($registration->registration_type) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Participants</p>
                <p class="text-lg font-semibold">{{ 1 + $registration->participants->count() }}</p>
                <p class="text-xs text-gray-500">(Registrant + {{ $registration->participants->count() }} members)</p>
            </div>
        </div>
    </div>
</div>

<!-- Registrant Card -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6 border-b bg-blue-50">
        <h3 class="text-lg font-semibold flex items-center">
            <i class="fas fa-user-check mr-2 text-blue-600"></i>
            Primary Registrant
        </h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-500">Full Name</p>
                <p class="font-semibold">{{ $registration->user->full_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p class="font-semibold">{{ $registration->user->email }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Nationality</p>
                <p class="font-semibold">{{ $registration->user->nationality ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Passport Number</p>
                <p class="font-semibold">{{ $registration->user->passport_number ?? 'Not provided' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Airport of Origin</p>
                <p class="font-semibold">{{ $registration->user->airport_of_origin ?? 'Not provided' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Passport Document</p>
                @if($registration->user->passport_file)
                    <div class="flex space-x-2">
                        <button onclick="openPassportPreview('{{ env('PARENT_APP_URL') }}/uploads/passports/{{ $registration->user->passport_file }}')" 
                                class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <a href="{{ env('PARENT_APP_URL') }}/uploads/passports/{{ $registration->user->passport_file }}" 
                           download 
                           class="text-green-600 hover:text-green-800 text-sm ml-2">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                @else
                    <button onclick="requestPassportForRegistrant()" 
                            class="text-orange-600 hover:text-orange-800 text-sm">
                        <i class="fas fa-envelope"></i> Request
                    </button>
                @endif
            </div>
        </div>
        <div class="mt-4 pt-4 border-t">
            <p class="text-sm text-gray-500 mb-2">Actions</p>
            <div class="flex flex-wrap gap-2">
                <button onclick="openPdfModal({{ $registration->id }})" 
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    <i class="fas fa-file-pdf"></i> Preview Invitation
                </button>
                <a href="{{ route('invitations.download', $registration) }}" 
                   class="inline-block px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 ml-2">
                    <i class="fas fa-download"></i> Download Invitation
                </a>
                <button onclick="sendInvitationToRegistrant()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 ml-2">
                    <i class="fas fa-paper-plane"></i> Send Invitation Email
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Additional Participants -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold flex items-center">
            <i class="fas fa-users mr-2 text-indigo-600"></i>
            Additional Participants ({{ $registration->participants->count() }})
        </h3>
    </div>
    <div class="p-6">
        @forelse($registration->participants as $participant)
        <div class="mb-6 pb-6 border-b last:border-b-0">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Full Name</p>
                    <p class="font-semibold">{{ $participant->full_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-semibold">{{ $participant->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Nationality</p>
                    <p class="font-semibold">{{ $participant->nationality ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Passport Number</p>
                    <p class="font-semibold">{{ $participant->passport_number ?? 'Not provided' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Airport of Origin</p>
                    <p class="font-semibold">{{ $participant->airport_of_origin ?? 'Not provided' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Passport Document</p>
                    @if($participant->passport_file)
                        <div class="flex space-x-2">
                            <button onclick="openPassportPreview('{{ env('PARENT_APP_URL') }}/uploads/passports/{{ $participant->passport_file }}')" 
                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <a href="{{ env('PARENT_APP_URL') }}/uploads/passports/{{ $participant->passport_file }}" 
                               download 
                               class="text-green-600 hover:text-green-800 text-sm ml-2">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    @else
                        <button onclick="requestPassportForParticipant({{ $participant->id }}, '{{ addslashes($participant->full_name) }}')" 
                                class="text-orange-600 hover:text-orange-800 text-sm ml-2">
                            <i class="fas fa-envelope"></i> Request
                        </button>
                    @endif
                </div>
                @if($participant->invitation_sent_at)
                <div>
                    <p class="text-sm text-gray-500">Invitation Sent</p>
                    <div class="text-sm">
                        <i class="fas fa-check-circle text-green-600"></i>
                        {{ $participant->invitation_sent_at->format('M d, Y') }}
                        @if($participant->invitationSentBy)
                            <span class="text-xs text-gray-500">by {{ $participant->invitationSentBy->username }}</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <button onclick="openParticipantPdfModal({{ $registration->id }}, {{ $participant->id }})" 
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm">
                    <i class="fas fa-file-pdf"></i> Preview Invitation
                </button>
                <button onclick="sendInvitationToParticipant({{ $participant->id }}, '{{ addslashes($participant->full_name) }}')" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm ml-2">
                    <i class="fas fa-paper-plane"></i> Send Invitation Email
                </button>
            </div>
        </div>
        @empty
        <p class="text-center text-gray-500">No additional participants</p>
        @endforelse
    </div>
</div>

<div class="mt-6">
    <a href="{{ route('registrations.index') }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left"></i> Back to Registrations
    </a>
</div>

<!-- Include PDF Preview Modal -->
@include('components.invitation-preview-modal')

<!-- Include Passport Preview Modal -->
@include('components.passport-preview-modal')

<script>
function openParticipantPdfModal(registrationId, participantId) {
    const modal = document.getElementById('pdfPreviewModal');
    const iframe = document.getElementById('pdfIframe');
    const loader = document.getElementById('pdfLoader');
    
    if (!modal || !iframe || !loader) {
        console.error('Modal elements not found');
        return;
    }
    
    // Reset iframe
    iframe.src = 'about:blank';
    
    // Show modal and loader
    modal.style.display = 'block';
    loader.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Store current URL for download (not used for participants, but keeping consistency)
    window.currentPdfUrl = '';
    
    // Create form and submit to iframe with participant ID
    setTimeout(function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("invitations.preview") }}';
        form.target = 'pdfIframe';
        form.style.display = 'none';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        form.appendChild(tokenInput);
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'registration_id';
        idInput.value = registrationId;
        form.appendChild(idInput);
        
        // Add participant ID
        const participantInput = document.createElement('input');
        participantInput.type = 'hidden';
        participantInput.name = 'participant_id';
        participantInput.value = participantId;
        form.appendChild(participantInput);
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
        // Hide loader after timeout
        setTimeout(function() {
            loader.style.display = 'none';
        }, 7000);
    }, 100);
}

function sendInvitationToRegistrant() {
    if (confirm('Send invitation email to {{ $registration->user->full_name }}?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('registrations.send-invitation', $registration) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function sendInvitationToParticipant(participantId, participantName) {
    if (confirm(`Send invitation email to ${participantName}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('registrations/' . $registration->id . '/participants') }}/${participantId}/send-invitation`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function requestPassportForRegistrant() {
    alert('Passport request for registrant should be handled from the Approved Delegates page.');
}

function requestPassportForParticipant(participantId, participantName) {
    if (confirm(`Send passport request email to ${participantName}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('registrations/' . $registration->id . '/participants') }}/${participantId}/request-passport`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

@endsection

