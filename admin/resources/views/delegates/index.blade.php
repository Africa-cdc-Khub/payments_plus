@extends('layouts.app')

@section('title', 'Manage Delegates')
@section('page-title', 'Manage Delegates')

@section('content')
<!-- Status Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-medium">Pending Review</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $statusCounts['pending'] ?? 0 }}</p>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <i class="fas fa-clock text-2xl text-yellow-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-medium">Approved</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $statusCounts['approved'] ?? 0 }}</p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-check-circle text-2xl text-green-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-medium">Rejected</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $statusCounts['rejected'] ?? 0 }}</p>
            </div>
            <div class="bg-red-100 rounded-full p-3">
                <i class="fas fa-times-circle text-2xl text-red-600"></i>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold">Delegate Registrations</h3>
            
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
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($delegates as $delegate)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $delegate->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $delegate->user->full_name }}</div>
                                <div class="text-sm text-gray-500">{{ $delegate->user->organization ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $delegate->user->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $delegate->user->delegate_category ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($delegate->status === 'pending')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>Pending
                                    </span>
                                @elseif($delegate->status === 'approved')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Approved
                                    </span>
                                @elseif($delegate->status === 'rejected')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i>Rejected
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ ucfirst($delegate->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $delegate->created_at ? $delegate->created_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('delegates.show', $delegate) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                
                                @can('manageDelegates', App\Models\Registration::class)
                                    @if($delegate->status === 'pending')
                                        <button type="button" 
                                                onclick="quickApprove({{ $delegate->id }})" 
                                                class="ml-3 text-green-600 hover:text-green-900"
                                                title="Quick Approve">
                                            <i class="fas fa-check-circle"></i> Approve
                                        </button>
                                        <button type="button" 
                                                onclick="openRejectModal({{ $delegate->id }}, '{{ $delegate->user->full_name }}')" 
                                                class="ml-3 text-red-600 hover:text-red-900"
                                                title="Reject">
                                            <i class="fas fa-times-circle"></i> Reject
                                        </button>
                                    @endif
                                @endcan
                                
                                @if($delegate->status === 'approved')
                                    @can('viewInvitation', App\Models\Registration::class)
                                        <button type="button" 
                                                onclick="openPdfModal({{ $delegate->id }})" 
                                                class="ml-3 text-purple-600 hover:text-purple-900"
                                                title="Preview Invitation">
                                            <i class="fas fa-file-pdf"></i> Preview
                                        </button>
                                        <a href="{{ route('invitations.download', $delegate) }}" class="ml-3 text-green-600 hover:text-green-900" title="Download Invitation">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No delegate registrations found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $delegates->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Include PDF Preview Modal -->
@include('components.invitation-preview-modal')

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Reject Delegate</h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="rejectForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-3">
                        Rejecting: <strong id="delegateName"></strong>
                    </p>
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason (Optional)
                    </label>
                    <textarea 
                        name="reason" 
                        id="rejection_reason" 
                        rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Provide a reason..."></textarea>
                </div>
                
                <div class="flex gap-3 justify-end">
                    <button type="button" 
                            onclick="closeRejectModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <i class="fas fa-times-circle mr-2"></i>Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Quick approve function
function quickApprove(delegateId) {
    if (!confirm('Are you sure you want to approve this delegate?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ url('/delegates/${delegateId}/approve') }}`;
    
    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = '{{ csrf_token() }}';
    form.appendChild(token);
    
    document.body.appendChild(form);
    form.submit();
}

function openRejectModal(delegateId, delegateName) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    const nameElement = document.getElementById('delegateName');
    
    form.action = `{{ url('/delegates/${delegateId}/reject') }}`;
    nameElement.textContent = delegateName;
    modal.classList.remove('hidden');
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    
    modal.classList.add('hidden');
    form.reset();
}

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
@endpush
@endsection
