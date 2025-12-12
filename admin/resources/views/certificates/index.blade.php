@extends('layouts.app')

@section('title', 'Certificates')
@section('page-title', 'Certificates')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <!-- Page Title Row -->
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Certificate Management</h3>
            <div class="flex gap-3">
                @if(auth('admin')->user()->role === 'admin')
                <button 
                    type="button" 
                    onclick="sendSelectedCertificates()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    title="Send certificates to selected participants"
                >
                    <i class="fas fa-check-square"></i> Send Selected
                </button>
                <button 
                    type="button" 
                    onclick="sendAllCertificates()"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                    title="Send certificates to all participants in the table"
                >
                    <i class="fas fa-certificate"></i> Send to All Participants
                </button>
                @endif
            </div>
        </div>
            
        <!-- Filter Form -->
        <form method="GET" class="bg-gray-50 p-4 rounded-lg">
            <div class="flex flex-col sm:flex-row gap-4 mb-4">
                <!-- Search Field -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1"></i>Search
                    </label>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Name, email, or registration ID..." 
                        value="{{ request('search') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-2">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-search mr-2"></i>Apply Filters
                </button>
                @if(request()->has('search'))
                <a href="{{ route('certificates.index') }}" class="flex-1 sm:flex-none px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-times mr-2"></i>Clear Filters
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 m-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 m-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Participants Table -->
    <div class="overflow-x-auto">
        <form id="bulkCertificateForm" method="POST" action="{{ route('certificates.send-bulk') }}">
            @csrf
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Registration ID
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($participants as $participant)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input 
                                type="checkbox" 
                                name="participants[{{ $loop->index }}][registration_id]" 
                                value="{{ $participant['registration_id'] }}"
                                class="participant-checkbox"
                                data-participant-id="{{ $participant['participant_id'] }}"
                            >
                            @if($participant['participant_id'])
                            <input 
                                type="hidden" 
                                name="participants[{{ $loop->index }}][participant_id]" 
                                value="{{ $participant['participant_id'] }}"
                            >
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $participant['name'] }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $participant['email'] }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">#{{ $participant['registration_id'] }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($participant['type'] === 'individual') bg-blue-100 text-blue-800
                                @elseif($participant['type'] === 'group_focal') bg-green-100 text-green-800
                                @else bg-purple-100 text-purple-800
                                @endif">
                                @if($participant['type'] === 'individual')
                                    Individual
                                @elseif($participant['type'] === 'group_focal')
                                    Group (Focal)
                                @else
                                    Group Participant
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $registration = $participant['registration'];
                                $isDelegate = $registration->package_id == config('app.delegate_package_id');
                                $status = $registration->payment_status === 'completed' ? 'Paid' : ($isDelegate && $registration->status === 'approved' ? 'Approved' : 'Unknown');
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($status === 'Paid') bg-green-100 text-green-800
                                @elseif($status === 'Approved') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button 
                                type="button"
                                onclick="previewCertificate({{ $participant['registration_id'] }}, {{ $participant['participant_id'] ?: 'null' }})"
                                class="text-blue-600 hover:text-blue-900 mr-3"
                                title="Preview Certificate"
                            >
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <a 
                                href="{{ route('certificates.download') }}?registration_id={{ $participant['registration_id'] }}@if($participant['participant_id'])&participant_id={{ $participant['participant_id'] }}@endif"
                                class="text-green-600 hover:text-green-900 mr-3"
                                title="Download Certificate"
                            >
                                <i class="fas fa-download"></i> Download
                            </a>
                            @php
                                $registration = $participant['registration'];
                                $isDelegate = $registration->package_id == config('app.delegate_package_id');
                                $isEligible = $registration->payment_status === 'completed' || ($isDelegate && $registration->status === 'approved');
                            @endphp
                            @if($isEligible)
                            <button 
                                type="button"
                                onclick="sendCertificate({{ $participant['registration_id'] }}, {{ $participant['participant_id'] ?: 'null' }}, '{{ addslashes($participant['name']) }}')"
                                class="text-indigo-600 hover:text-indigo-900"
                                title="Send Certificate via Email"
                            >
                                <i class="fas fa-paper-plane"></i> Send
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            No eligible participants found for certificates.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    </div>

    <!-- Pagination -->
    @if($lastPage > 1)
    <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing {{ (($currentPage - 1) * $perPage) + 1 }} to {{ min($currentPage * $perPage, $total) }} of {{ $total }} participants
            </div>
            <div class="flex space-x-2">
                @if($currentPage > 1)
                <a href="{{ route('certificates.index', array_merge(request()->query(), ['page' => $currentPage - 1])) }}" 
                   class="px-3 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Previous
                </a>
                @endif
                @if($currentPage < $lastPage)
                <a href="{{ route('certificates.index', array_merge(request()->query(), ['page' => $currentPage + 1])) }}" 
                   class="px-3 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Next
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Certificate Preview Modal -->
<div id="certificatePreviewModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full"
 style="display: none; z-index: 10000; position:absolute; background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-6xl shadow-2xl rounded-lg bg-white"
     style="max-width: 90%; margin:0 auto; padding:10px; top:5%; height:90%;">
        <div class="h-full flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4 border-b pb-3">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-certificate text-green-500 mr-2"></i>
                    Certificate Preview
                </h3>
                <button type="button" onclick="closeCertificateModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 relative" style="height: calc(100% - 80px);">
                <div id="certificateLoader" class="absolute inset-0 flex justify-center items-center bg-white bg-opacity-90" style="display: flex;">
                    <div class="text-center">
                        <div class="spinner border-4 border-gray-300 border-t-green-600 rounded-full w-12 h-12 animate-spin mx-auto mb-4"></div>
                        <p class="text-gray-600">Loading certificate PDF...</p>
                    </div>
                </div>
                <iframe id="certificateIframe" 
                        src="" 
                        style="width: 100%; height: 100%; border: none; display: none;">
                </iframe>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.participant-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function previewCertificate(registrationId, participantId) {
    const modal = document.getElementById('certificatePreviewModal');
    const iframe = document.getElementById('certificateIframe');
    const loader = document.getElementById('certificateLoader');
    
    if (!modal || !iframe || !loader) {
        console.error('Certificate modal elements not found');
        return;
    }
    
    // Show modal and loader
    modal.style.display = 'block';
    loader.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Build preview URL
    let url = `{{ route('certificates.preview') }}?registration_id=${registrationId}`;
    if (participantId) {
        url += `&participant_id=${participantId}`;
    }
    
    // Load PDF in iframe
    iframe.src = url;
}

function closeCertificateModal() {
    const modal = document.getElementById('certificatePreviewModal');
    const iframe = document.getElementById('certificateIframe');
    const loader = document.getElementById('certificateLoader');
    
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        if (iframe) {
            iframe.src = '';
            iframe.style.display = 'none';
        }
        if (loader) {
            loader.style.display = 'flex';
        }
    }
}

function sendCertificate(registrationId, participantId, participantName) {
    if (confirm(`Send certificate to ${participantName}?\n\nThis will queue a certificate email with PDF attachment.`)) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("certificates.send") }}';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add registration_id
        const regInput = document.createElement('input');
        regInput.type = 'hidden';
        regInput.name = 'registration_id';
        regInput.value = registrationId;
        form.appendChild(regInput);
        
        // Add participant_id if provided
        if (participantId) {
            const partInput = document.createElement('input');
            partInput.type = 'hidden';
            partInput.name = 'participant_id';
            partInput.value = participantId;
            form.appendChild(partInput);
        }
        
        // Add to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}

function sendSelectedCertificates() {
    const checkboxes = document.querySelectorAll('.participant-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('Please select at least one participant to send certificates to.');
        return;
    }
    
    if (confirm(`Send certificates to ${checkboxes.length} selected participant(s)?\n\nThis will queue certificate emails. This action may take some time to complete.`)) {
        // Build participants array
        const participants = [];
        checkboxes.forEach((checkbox, index) => {
            const participantId = checkbox.getAttribute('data-participant-id');
            participants.push({
                registration_id: checkbox.value,
                participant_id: participantId || null
            });
        });
        
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("certificates.send-bulk") }}';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add participants data
        participants.forEach((p, index) => {
            const regInput = document.createElement('input');
            regInput.type = 'hidden';
            regInput.name = `participants[${index}][registration_id]`;
            regInput.value = p.registration_id;
            form.appendChild(regInput);
            
            if (p.participant_id) {
                const partInput = document.createElement('input');
                partInput.type = 'hidden';
                partInput.name = `participants[${index}][participant_id]`;
                partInput.value = p.participant_id;
                form.appendChild(partInput);
            }
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}

function sendAllCertificates() {
    const allCheckboxes = document.querySelectorAll('.participant-checkbox');
    const totalCount = allCheckboxes.length;
    
    if (totalCount === 0) {
        alert('No participants found to send certificates to.');
        return;
    }
    
    if (confirm(`Send certificates to ALL ${totalCount} participant(s) in the table?\n\nThis will queue certificate emails for all participants. This action may take some time to complete.`)) {
        // Build participants array for all participants
        const participants = [];
        allCheckboxes.forEach((checkbox, index) => {
            const participantId = checkbox.getAttribute('data-participant-id');
            participants.push({
                registration_id: checkbox.value,
                participant_id: participantId || null
            });
        });
        
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("certificates.send-bulk") }}';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add participants data
        participants.forEach((p, index) => {
            const regInput = document.createElement('input');
            regInput.type = 'hidden';
            regInput.name = `participants[${index}][registration_id]`;
            regInput.value = p.registration_id;
            form.appendChild(regInput);
            
            if (p.participant_id) {
                const partInput = document.createElement('input');
                partInput.type = 'hidden';
                partInput.name = `participants[${index}][participant_id]`;
                partInput.value = p.participant_id;
                form.appendChild(partInput);
            }
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Handle iframe load
document.addEventListener('DOMContentLoaded', function() {
    const iframe = document.getElementById('certificateIframe');
    if (iframe) {
        iframe.addEventListener('load', function() {
            const loader = document.getElementById('certificateLoader');
            if (loader) {
                loader.style.display = 'none';
            }
            iframe.style.display = 'block';
        });
    }
});

// ESC close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeCertificateModal();
});

// Close on backdrop click
document.getElementById('certificatePreviewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCertificateModal();
    }
});
</script>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

@endsection
