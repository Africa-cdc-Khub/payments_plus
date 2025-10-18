@extends('layouts.app')

@section('title', 'Registrations')
@section('page-title', 'Registrations')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <!-- Page Title Row -->
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-800">All Registrations</h3>
            @if(auth('admin')->user()->role === 'admin')
            <button 
                type="button" 
                id="bulkVoidBtn"
                onclick="voidSelected()"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 hidden"
            >
                <i class="fas fa-ban"></i> Void Selected (<span id="selectedCount">0</span>)
            </button>
            @endif
        </div>

        <!-- Filter Form -->
        <form method="GET" class="bg-gray-50 p-4 rounded-lg">
            <!-- Filter Fields in One Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <!-- Registration ID Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-hashtag mr-1"></i>Registration ID
                    </label>
                    <input 
                        type="text" 
                        name="registration_id" 
                        placeholder="Registration ID..." 
                        value="{{ request('registration_id') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                </div>

                <!-- Search Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1"></i>Search
                    </label>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Name or email..." 
                        value="{{ request('search') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-flag mr-1"></i>Status
                    </label>
                    <select 
                        name="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Paid</option>
                        <option value="delegates" {{ request('status') === 'delegates' ? 'selected' : '' }}>Delegates</option>
                        <option value="approved_delegates" {{ request('status') === 'approved_delegates' ? 'selected' : '' }}>Approved Delegates</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected Delegate</option>
                        <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Voided</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons Below Form -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-2">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-search mr-2"></i>Apply Filters
                </button>
                @if(request()->hasAny(['registration_id', 'search', 'status']))
                <a href="{{ route('registrations.index') }}" class="flex-1 sm:flex-none px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-times mr-2"></i>Clear Filters
                </a>
                @endif
                <a href="{{ route('registrations.export', request()->query()) }}" class="flex-1 sm:flex-none px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </a>
            </div>
        </form>
    </div>

    <div class="p-6">
    <!-- Showing records info and per-page selector -->
    <div class="mb-4 mt-2 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <p class="text-sm text-gray-700 leading-5">
            Showing
            @if ($registrations->firstItem())
                <span class="font-medium">{{ $registrations->firstItem() }}</span>
                to
                <span class="font-medium">{{ $registrations->lastItem() }}</span>
            @else
                {{ $registrations->count() }}
            @endif
            of
            <span class="font-medium">{{ $registrations->total() }}</span>
            registrations
        </p>
        
        <!-- Per-page selector -->
        <x-per-page-selector :paginator="$registrations" :current-per-page="request('per_page', 50)" />
    </div>

            <div class="table-container">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            @if(auth('admin')->user()->role === 'admin')
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <input type="checkbox" id="selectAll" onclick="toggleSelectAll()" class="rounded">
                            </th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Name</span>
                                    @if(request('sort') == 'name')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @else
                                        <i class="fas fa-sort text-xs opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'email', 'direction' => request('sort') == 'email' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Email</span>
                                    @if(request('sort') == 'email')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @else
                                        <i class="fas fa-sort text-xs opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'package', 'direction' => request('sort') == 'package' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Package</span>
                                    @if(request('sort') == 'package')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @else
                                        <i class="fas fa-sort text-xs opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'amount', 'direction' => request('sort') == 'amount' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Amount</span>
                                    @if(request('sort') == 'amount')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @else
                                        <i class="fas fa-sort text-xs opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            @if(!in_array(auth('admin')->user()->role, ['executive']))
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marked By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invitation Sent</th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($registrations as $index => $registration)
                        @php
                            $isDelegate = $registration->package_id == config('app.delegate_package_id');
                            $canReceiveInvitation = $registration->isPaid() || ($isDelegate && $registration->status === 'approved');
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $registrations->firstItem() + $index }}
                            </td>
                            @if(auth('admin')->user()->role === 'admin')
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $canVoid = $registration->isPending() 
                                        && !$registration->isVoided() 
                                        && !($isDelegate && $registration->status === 'approved');
                                @endphp
                                @if($canVoid)
                                <input 
                                    type="checkbox" 
                                    class="registration-checkbox rounded" 
                                    value="{{ $registration->id }}"
                                    data-name="{{ $registration->user->full_name }}"
                                    onchange="updateBulkVoidButton()"
                                >
                                @endif
                            </td>
                            @endif
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
                            @if(!in_array(auth('admin')->user()->role, ['executive']))
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($registration->isVoided())
                                    {{-- Voided status takes precedence --}}
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <i class="fas fa-ban mr-1"></i>Voided
                                    </span>
                                @elseif($isDelegate)
                                    {{-- For delegates, show delegate status --}}
                                    @if($registration->status === 'approved')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-user-check mr-1"></i>Approved Delegate
                                        </span>
                                    @elseif($registration->status === 'rejected')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-user-times mr-1"></i>Rejected Delegate
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-hourglass-half mr-1"></i>Delegate Pending
                                        </span>
                                    @endif
                                @else
                                    {{-- For non-delegates, show payment status --}}
                                    @if($registration->isPaid())
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Paid
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ ($registration->package_id == config('app.delegate_package_id')  ) ? 'N/A' : 'Pending Payment' }}<i class="fas fa-clock mr-1"></i>
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($registration->isVoided() && $registration->voidedBy)
                                    <div class="flex items-center" title="Voided by {{ $registration->voidedBy->full_name ?? $registration->voidedBy->username }}">
                                        <i class="fas fa-ban text-red-600 mr-1"></i>
                                        <span>{{ $registration->voidedBy->username ?? 'Admin' }}</span>
                                    </div>
                                    @if($registration->void_reason)
                                        <div class="text-xs text-gray-400 mt-1" title="{{ $registration->void_reason }}">
                                            <i class="fas fa-comment-dots"></i> 
                                            {{ Str::limit($registration->void_reason, 30) }}
                                        </div>
                                    @endif
                                @elseif($registration->payment && $registration->payment->completed_by)
                                    <div class="flex items-center" title="Manually marked as paid by {{ $registration->payment->completedBy->full_name ?? $registration->payment->completedBy->username }}">
                                        <i class="fas fa-user-check text-green-600 mr-1"></i>
                                        <span>{{ $registration->payment->completedBy->username ?? 'Admin' }}</span>
                                    </div>
                                    @if($registration->payment->manual_payment_remarks)
                                        <div class="text-xs text-gray-400 mt-1" title="{{ $registration->payment->manual_payment_remarks }}">
                                            <i class="fas fa-comment-dots"></i> 
                                            {{ Str::limit($registration->payment->manual_payment_remarks, 30) }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($registration->invitation_sent_at)
                                    <div class="flex items-center" title="Invitation sent by {{ $registration->invitationSentBy->full_name ?? $registration->invitationSentBy->username ?? 'Admin' }}">
                                        <i class="fas fa-envelope text-blue-600 mr-1"></i>
                                        <div>
                                            <div>{{ $registration->invitation_sent_at->format('M d, Y') }}</div>
                                            <div class="text-xs text-gray-400">{{ $registration->invitationSentBy->username ?? 'Admin' }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($registration->isVoided())
                                    {{-- Voided registrations only show View and Undo Void --}}
                                    <a href="{{ route('registrations.show', $registration) }}" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    
                                    @if(in_array(auth('admin')->user()->role, ['admin', 'secretariat']) && $registration->registration_type !== 'individual')
                                    <a href="{{ route('registration-participants.index', $registration) }}" 
                                       class="ml-3 text-red-600 hover:text-red-900"
                                       title="View Participants">
                                        <i class="fas fa-users"></i> Participants
                                    </a>
                                    @endif
                                    
                                    @if(auth('admin')->user()->role === 'admin')
                                    <button type="button" 
                                            onclick="undoVoid({{ $registration->id }}, '{{ addslashes($registration->user->full_name) }}')" 
                                            class="ml-3 text-green-600 hover:text-green-900"
                                            title="Undo Void">
                                        <i class="fas fa-undo"></i> Undo Void
                                    </button>
                                    @endif
                                @else
                                    {{-- Normal registrations show all actions --}}
                                    <a href="{{ route('registrations.show', $registration) }}" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    
                                    @if(in_array(auth('admin')->user()->role, ['admin', 'secretariat']) && $registration->registration_type !== 'individual')
                                    <a href="{{ route('registration-participants.index', $registration) }}" 
                                       class="ml-3 text-red-600 hover:text-red-900"
                                       title="View Participants">
                                        <i class="fas fa-users"></i> Participants
                                    </a>
                                    @endif
                                    
                                    @can('markAsPaid', App\Models\Registration::class)
                                    @if(!$registration->isPaid() && !$isDelegate)
                                    <button type="button" 
                                            onclick="openMarkPaidModal({{ $registration->id }}, '{{ addslashes($registration->user->full_name) }}', '{{ $registration->total_amount }}')" 
                                            class="ml-3 text-orange-600 hover:text-orange-900"
                                            title="Mark as Paid">
                                        <i class="fas fa-money-bill-wave"></i> Mark Paid
                                    </button>
                                    @endif
                                    @endcan
                                    
                                    @can('viewInvitation', App\Models\Registration::class)
                                    @if($canReceiveInvitation)
                                    <button type="button" 
                                            onclick="openPdfModal({{ $registration->id }})" 
                                            class="ml-3 text-purple-600 hover:text-purple-900"
                                            title="Preview Invitation">
                                        <i class="fas fa-file-pdf"></i> Preview
                                    </button>
                                    <a href="{{ route('invitations.download', $registration) }}" class="ml-3 text-green-600 hover:text-green-900" title="Download Invitation Letter">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    @endif
                                    
                                    @if($registration->isPaid() && in_array(auth('admin')->user()->role, ['admin', 'finance']))
                                    <a href="{{ route('registrations.invoice', $registration) }}" class="ml-3 text-blue-600 hover:text-blue-900" title="Generate Invoice">
                                        <i class="fas fa-file-invoice"></i> Invoice
                                    </a>
                                    @endif
                                    
                                    @if(auth('admin')->user()->role === 'admin')
                                    <button type="button" 
                                            onclick="sendInvitationEmail({{ $registration->id }}, '{{ addslashes($registration->user->full_name) }}')" 
                                            class="ml-3 text-indigo-600 hover:text-indigo-900"
                                            title="Send Invitation Email">
                                        <i class="fas fa-envelope"></i> Send Invitation
                                    </button>
                                    @endif
                                    @endcan
                                    
                                    @php
                                        $canVoid = auth('admin')->user()->role === 'admin' 
                                            && $registration->isPending() 
                                            && !$registration->isVoided() 
                                            && !($isDelegate && $registration->status === 'approved');
                                    @endphp
                                    @if($canVoid)
                                    <button type="button" 
                                            onclick="openMarkVoidModal({{ $registration->id }}, '{{ addslashes($registration->user->full_name) }}')" 
                                            class="ml-3 text-red-600 hover:text-red-900"
                                            title="Void Registration">
                                        <i class="fas fa-ban"></i> Void
                                    </button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth('admin')->user()->role === 'executive' ? '6' : (auth('admin')->user()->role === 'admin' ? '10' : '9') }}" class="px-6 py-4 text-center text-gray-500">No registrations found</td>
                        </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </div>

        <div class="mt-6">
                {{ $registrations->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Include PDF Preview Modal -->
@include('components.invitation-preview-modal')

<!-- Include Mark Paid Modal -->
@include('components.mark-paid-modal')

<!-- Include Mark Void Modal -->
@include('components.mark-void-modal')

<script>
// Bulk void functions
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.registration-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    updateBulkVoidButton();
}

function updateBulkVoidButton() {
    const checkboxes = document.querySelectorAll('.registration-checkbox:checked');
    const bulkBtn = document.getElementById('bulkVoidBtn');
    const countSpan = document.getElementById('selectedCount');
    
    if (bulkBtn && countSpan) {
        if (checkboxes.length > 0) {
            bulkBtn.classList.remove('hidden');
            countSpan.textContent = checkboxes.length;
            } else {
            bulkBtn.classList.add('hidden');
            countSpan.textContent = '0';
        }
    }
}

function voidSelected() {
    const checkboxes = document.querySelectorAll('.registration-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one registration to void.');
                return;
            }

    const registrationIds = Array.from(checkboxes).map(cb => cb.value);
    openBulkVoidModal(registrationIds);
}
            
function sendInvitationEmail(registrationId, delegateName) {
    if (confirm(`Send invitation email to ${delegateName}?\n\nThis will queue an email with their invitation letter attached.`)) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{  url('registrations') }}/${registrationId}/send-invitation`;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}

function undoVoid(registrationId, registrantName) {
    if (confirm(`Undo void for ${registrantName}?\n\nThis will restore the registration to pending status.`)) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{  url('registrations') }}/${registrationId}/undo-void`;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

@endsection
