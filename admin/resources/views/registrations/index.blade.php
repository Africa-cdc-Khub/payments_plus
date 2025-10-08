@extends('layouts.app')

@section('title', 'Registrations')
@section('page-title', 'Registrations')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold">All Registrations</h3>
            
            <div class="flex space-x-4">
                <form method="GET" class="flex space-x-2">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search by name or email..." 
                        value="{{ request('search') }}"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <select 
                        name="status" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Paid</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="p-6">
        <form id="invitationForm" method="POST" action="{{ route('invitations.send') }}">
            @csrf
            <div class="mb-4 flex justify-between items-center">
                <div>
                    <button type="button" id="selectAllPaid" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-check-square"></i> Select All Paid
                    </button>
                    <button type="button" id="deselectAll" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                        <i class="fas fa-square"></i> Deselect All
                    </button>
                </div>
                <div>
                    <button type="button" id="previewSelected" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                        <i class="fas fa-eye"></i> Preview Invitation
                    </button>
                    <button type="submit" id="sendInvitations" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                        <i class="fas fa-paper-plane"></i> Send Invitations
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <input type="checkbox" id="selectAllCheckbox" class="rounded">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Package</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($registrations as $registration)
                        <tr>
                            <td class="px-6 py-4">
                                @if($registration->isPaid())
                                <input 
                                    type="checkbox" 
                                    name="registration_ids[]" 
                                    value="{{ $registration->id }}"
                                    class="registration-checkbox rounded"
                                    data-paid="true"
                                >
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $registration->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $registration->user->full_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $registration->user->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $registration->package->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ number_format($registration->total_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($registration->isPaid())
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('registrations.show', $registration) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if($registration->isPaid())
                                <a href="{{ route('invitations.download', $registration) }}" class="ml-3 text-green-600 hover:text-green-900">
                                    <i class="fas fa-download"></i> PDF
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No registrations found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <div class="mt-6">
            {{ $registrations->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    'use strict';
    
    let currentPreviewRegistrationId = null;

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Registrations page loaded');
        console.log('Number of paid checkboxes:', document.querySelectorAll('.registration-checkbox[data-paid="true"]').length);
        
        // Function to update button states
        function updateButtonStates() {
            const selectedCount = document.querySelectorAll('.registration-checkbox:checked').length;
            console.log('Selected count:', selectedCount);
            
            const previewBtn = document.getElementById('previewSelected');
            const sendBtn = document.getElementById('sendInvitations');
            
            if (selectedCount > 0) {
                previewBtn.disabled = false;
                sendBtn.disabled = false;
            } else {
                previewBtn.disabled = true;
                sendBtn.disabled = true;
            }
        }
        
        // Select all paid registrations
        document.getElementById('selectAllPaid').addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Select all paid clicked');
            
            const checkboxes = document.querySelectorAll('.registration-checkbox[data-paid="true"]');
            console.log('Found checkboxes:', checkboxes.length);
            
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = true;
            });
            
            updateButtonStates();
        });

        // Deselect all
        document.getElementById('deselectAll').addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Deselect all clicked');
            
            document.querySelectorAll('.registration-checkbox').forEach(function(checkbox) {
                checkbox.checked = false;
            });
            document.getElementById('selectAllCheckbox').checked = false;
            
            updateButtonStates();
        });

        // Select all checkbox in header
        document.getElementById('selectAllCheckbox').addEventListener('change', function() {
            console.log('Select all checkbox changed');
            const isChecked = this.checked;
            
            document.querySelectorAll('.registration-checkbox[data-paid="true"]').forEach(function(checkbox) {
                checkbox.checked = isChecked;
            });
            
            updateButtonStates();
        });
        
        // Update button states when individual checkboxes change
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('registration-checkbox')) {
                updateButtonStates();
            }
        });

        // Preview selected invitation
        document.getElementById('previewSelected').addEventListener('click', function(e) {
            e.preventDefault();
            const selected = document.querySelector('.registration-checkbox:checked');
            
            if (!selected) {
                alert('Please select at least one registration to preview');
                return;
            }

            currentPreviewRegistrationId = selected.value;
            console.log('Preview registration:', currentPreviewRegistrationId);
            
            // Open preview in new tab
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("invitations.preview") }}';
            form.target = '_blank';

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            form.appendChild(tokenInput);

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'registration_id';
            idInput.value = currentPreviewRegistrationId;
            form.appendChild(idInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        });

        // Validate form submission
        document.getElementById('invitationForm').addEventListener('submit', function(e) {
            const selectedCount = document.querySelectorAll('.registration-checkbox:checked').length;
            
            if (selectedCount === 0) {
                e.preventDefault();
                alert('Please select at least one registration to send invitations');
                return false;
            }

            if (!confirm(`Are you sure you want to send invitations to ${selectedCount} registration(s)?`)) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });
        
        // Initialize button states on page load
        updateButtonStates();
    });
})();
</script>
@endpush

@endsection
