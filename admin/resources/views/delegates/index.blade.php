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
        <h3 class="text-lg font-semibold mb-4">Delegate Registrations</h3>

        <!-- Filter Form - Always Visible Horizontal Layout -->
        <form method="GET" class="bg-gray-50 p-4 rounded-lg">
            <div class="flex flex-wrap gap-3 mb-6">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Name or email..." 
                        value="{{ request('search') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select 
                        name="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Delegate Category</label>
                    <select 
                        name="delegate_category" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        <option value="">All Categories</option>
                        @foreach($delegateCategories as $category)
                            <option value="{{ $category }}" {{ request('delegate_category') === $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                    <select 
                        name="country" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        <option value="">All Countries</option>
                        @foreach($countries as $country)
                            <option value="{{ $country }}" {{ request('country') === $country ? 'selected' : '' }}>
                                {{ $country }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 whitespace-nowrap">
                    <i class="fas fa-search"></i> Apply Filters
                </button>
                <a href="{{ route('delegates.index') }}" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 whitespace-nowrap">
                    <i class="fas fa-times"></i> Clear
                </a>
                <a href="{{ route('delegates.export', request()->query()) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 whitespace-nowrap">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
        </form>

        <!-- Filter Summary -->
        @if(request()->hasAny(['search', 'status', 'delegate_category', 'country']))
        <div class="mt-4 flex flex-wrap gap-2">
            <span class="text-sm text-gray-600">Active filters:</span>
            @if(request('search'))
                <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Search: "{{ request('search') }}"
                </span>
            @endif
            @if(request('status'))
                <span class="inline-flex items-center px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">
                    Status: {{ ucfirst(request('status')) }}
                </span>
            @endif
            @if(request('delegate_category'))
                <span class="inline-flex items-center px-3 py-1 bg-orange-100 text-orange-800 text-sm rounded-full">
                    Category: {{ request('delegate_category') }}
                </span>
            @endif
            @if(request('country'))
                <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    Country: {{ request('country') }}
                </span>
            @endif
        </div>
        @endif
    </div>

    <div class="p-6 pt-8">
        <!-- Showing records info -->
        <div class="mb-4 mt-2">
            <p class="text-sm text-gray-700 leading-5">
                Showing
                @if ($delegates->firstItem())
                    <span class="font-medium">{{ $delegates->firstItem() }}</span>
                    to
                    <span class="font-medium">{{ $delegates->lastItem() }}</span>
                @else
                    {{ $delegates->count() }}
                @endif
                of
                <span class="font-medium">{{ $delegates->total() }}</span>
                delegates
            </p>
        </div>
        
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
                                    @elseif(auth('admin')->user()->role === 'admin')
                                        @if($delegate->status === 'approved')
                                            <button type="button" 
                                                    onclick="resetToPending({{ $delegate->id }}, 'Cancel Approval', 'Are you sure you want to cancel the approval for {{ addslashes($delegate->user->full_name) }}? This will move them back to pending status.')" 
                                                    class="ml-3 text-orange-600 hover:text-orange-900"
                                                    title="Cancel Approval">
                                                <i class="fas fa-undo"></i> Cancel Approval
                                            </button>
                                        @elseif($delegate->status === 'rejected')
                                            <button type="button" 
                                                    onclick="resetToPending({{ $delegate->id }}, 'Recall Rejection', 'Are you sure you want to recall the rejection for {{ addslashes($delegate->user->full_name) }}? This will move them back to pending status.')" 
                                                    class="ml-3 text-blue-600 hover:text-blue-900"
                                                    title="Recall Rejection">
                                                <i class="fas fa-undo"></i> Recall Rejection
                                            </button>
                                        @endif
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
                {{ $delegates->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Include PDF Preview Modal -->
@include('components.invitation-preview-modal')

<!-- Include Reject Delegate Modal -->
@include('components.reject-delegate-modal')

@push('scripts')
<script>
// Quick approve function
function quickApprove(delegateId) {
    if (!confirm('Are you sure you want to approve this delegate?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ url('delegates') }}/${delegateId}/approve`;
    
    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = '{{ csrf_token() }}';
    form.appendChild(token);
    
    document.body.appendChild(form);
    form.submit();
}

// Reset to pending function (cancel approval or recall rejection)
function resetToPending(delegateId, actionTitle, confirmMessage) {
    if (!confirm(confirmMessage)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ url('delegates') }}/${delegateId}/reset-to-pending`;
    
    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = '{{ csrf_token() }}';
    form.appendChild(token);
    
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
@endsection
