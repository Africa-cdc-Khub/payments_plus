@extends('layouts.app')

@section('title', 'Approved Delegates')
@section('page-title', 'Approved Delegates')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Approved Delegates List</h3>
            
            <form method="GET" action="{{ route('approved-delegates.export') }}" class="inline">
                @if(request('delegate_category'))
                    <input type="hidden" name="delegate_category" value="{{ request('delegate_category') }}">
                @endif
                @if(request('country'))
                    <input type="hidden" name="country" value="{{ request('country') }}">
                @endif
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if(request('travel_processed') !== null)
                    <input type="hidden" name="travel_processed" value="{{ request('travel_processed') }}">
                @endif
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
            </form>
        </div>

        <!-- Filter Form - Always Visible Horizontal Layout -->
        <form method="GET" class="bg-gray-50 p-4 rounded-lg mb-4">
            <div class="flex flex-wrap gap-3 mb-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Name or email..." 
                        value="{{ request('search') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Delegate Category</label>
                    <select 
                        name="delegate_category" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Countries</option>
                        @foreach($countries as $country)
                            <option value="{{ $country }}" {{ request('country') === $country ? 'selected' : '' }}>
                                {{ $country }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(auth('admin')->user()->role === 'travels')
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Travel Status</label>
                    <select 
                        name="travel_processed" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Status</option>
                        <option value="0" {{ request('travel_processed') === '0' ? 'selected' : '' }}>Unprocessed</option>
                        <option value="1" {{ request('travel_processed') === '1' ? 'selected' : '' }}>Processed</option>
                    </select>
                </div>
                @endif
            </div>

            <div class="flex gap-2 mt-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 whitespace-nowrap">
                    <i class="fas fa-search"></i> Apply
                </button>
                <a href="{{ route('approved-delegates.index') }}" class="px-4 ml-2 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 whitespace-nowrap">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </form>

        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-green-900">Total Approved</h4>
                        <p class="text-2xl font-bold text-green-700">{{ $delegates->total() }}</p>
                    </div>
                </div>
            </div>

            @if(request('delegate_category'))
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-filter text-blue-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-900">Filtered Category</h4>
                        <p class="text-lg font-bold text-blue-700">{{ request('delegate_category') }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if(request('country'))
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-globe text-purple-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-purple-900">Filtered Country</h4>
                        <p class="text-lg font-bold text-purple-700">{{ request('country') }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

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
            approved delegates
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Country</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Passport No.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Airport of Origin</th>
                    @if(auth('admin')->user()->role === 'travels')
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Travel Status</th>
                    @endif
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approved Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($delegates as $index => $delegate)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $delegates->firstItem() + $index }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $delegate->id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $delegate->user->full_name }}</div>
                        @if($delegate->user->title)
                            <div class="text-xs text-gray-500">{{ $delegate->user->title }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $delegate->user->email }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $delegate->user->organization ?? '-' }}</div>
                        @if($delegate->user->position)
                            <div class="text-xs text-gray-500">{{ $delegate->user->position }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $delegate->user->country ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($delegate->user->delegate_category)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $delegate->user->delegate_category }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if(in_array(auth('admin')->user()->role, ['admin', 'travels']))
                            <div class="flex items-center space-x-2">
                             
                                <span class="text-gray-600">
                                    {{ $delegate->user->passport_number ?? '-' }}
                                </span>
                                
                                @if($delegate->user->passport_file)
                                    <button 
                                        onclick="openPassportPreview('{{ env('PARENT_APP_URL') }}/uploads/passports/{{ $delegate->user->passport_file }}')" 
                                        class="inline-flex items-center px-3 py-1 px-2 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        <small class="text-xs text-gray-500">View Attachment</small>
                                    </button>
                                @endif
                                {{-- <button 
                                        onclick="requestPassportEmail({{ $delegate->id }}, '{{ $delegate->user->full_name }}')" 
                                        class="inline-flex items-center px-3 py-1 border border-orange-300 text-xs font-medium rounded-md text-orange-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                                    >
                                        <i class="fas fa-envelope mr-1"></i> Send Request For Passport
                                    </button> --}}
                            </div>
                        @else
                            <span class="text-gray-400">••••••••</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if(in_array(auth('admin')->user()->role, ['admin', 'travels']))
                            {{ $delegate->user->airport_of_origin ?? '-' }}
                        @else
                            <span class="text-gray-400">••••••••</span>
                        @endif
                    </td>
                    @if(auth('admin')->user()->role === 'travels')
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($delegate->travel_processed)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle"></i> Processed
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock"></i> Pending
                            </span>
                        @endif
                    </td>
                    @endif
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $delegate->updated_at ? $delegate->updated_at->format('M d, Y') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('delegates.show', $delegate) }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i> View
                        </a>
                        
                        @can('viewInvitation', App\Models\Registration::class)
                        <button type="button" 
                                onclick="openPdfModal({{ $delegate->id }})" 
                                class="ml-3 text-purple-600 hover:text-purple-900"
                                title="Preview Invitation">
                            <i class="fas fa-file-pdf"></i> Invitation
                        </button>
                        @endcan

                        @if(auth('admin')->user()->role === 'travels')
                        <button type="button"
                                onclick="openTravelProcessedModal({{ $delegate->id }}, '{{ addslashes($delegate->user->full_name) }}', {{ $delegate->travel_processed ? 'true' : 'false' }})"
                                class="ml-3 text-{{ $delegate->travel_processed ? 'orange' : 'green' }}-600 hover:text-{{ $delegate->travel_processed ? 'orange' : 'green' }}-900"
                                title="{{ $delegate->travel_processed ? 'Mark as Unprocessed' : 'Mark as Processed' }}">
                            <i class="fas fa-{{ $delegate->travel_processed ? 'undo' : 'check' }}"></i> 
                            {{ $delegate->travel_processed ? 'Unmark Processed' : 'Mark Processed' }}
                        </button>
                        @endif
                        
                        @if(auth('admin')->user()->role === 'admin')
                        <button type="button"
                                onclick="openTravelProcessedModal({{ $delegate->id }}, '{{ addslashes($delegate->user->full_name) }}', {{ $delegate->travel_processed ? 'true' : 'false' }})"
                                class="ml-3 text-{{ $delegate->travel_processed ? 'orange' : 'green' }}-600 hover:text-{{ $delegate->travel_processed ? 'orange' : 'green' }}-900"
                                title="{{ $delegate->travel_processed ? 'Mark as Unprocessed' : 'Mark as Processed' }}">
                            <i class="fas fa-{{ $delegate->travel_processed ? 'undo' : 'check' }}"></i> 
                            {{ $delegate->travel_processed ? 'Unmark Processed' : 'Mark Processed' }}
                        </button>
                        <button type="button" 
                                onclick="cancelApproval({{ $delegate->id }}, '{{ addslashes($delegate->user->full_name) }}')" 
                                class="ml-3 text-red-600 hover:text-red-900"
                                title="Cancel Approval">
                            <i class="fas fa-times-circle"></i> Cancel Approval
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth('admin')->user()->role === 'travels' ? '11' : '10' }}" class="px-6 py-4 text-center text-gray-500">
                        No approved delegates found
                        @if(request()->hasAny(['search', 'delegate_category', 'country', 'travel_processed']))
                            matching your filters
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-6">
        {{ $delegates->appends(request()->query())->links() }}
    </div>
</div>

<!-- Include PDF Preview Modal -->
@include('components.invitation-preview-modal')

<!-- Include Mark Travel Processed Modal -->
@include('components.mark-travel-processed-modal')

<!-- Include Passport Preview Modal (Admin and Travels roles) -->
@if(in_array(auth('admin')->user()->role, ['admin', 'travels']))
    @include('components.passport-preview-modal')
@endif

<script>
function requestPassportEmail(delegateId, delegateName) {
    if (confirm(`Send passport request email to ${delegateName}?`)) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{  url('/approved-delegates/${delegateId}/request-passport') }}`;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add to body and submit
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
}

function cancelApproval(delegateId, delegateName) {
    if (confirm(`Are you sure you want to cancel the approval for ${delegateName}?\n\nThis will move them back to pending status and they will need to be re-reviewed.`)) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('delegates') }}/${delegateId}/reset-to-pending`;
        
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

