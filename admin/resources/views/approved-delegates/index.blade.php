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

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Country</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Passport No.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Airport of Origin</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approved Date</th>
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
                        @if(auth('admin')->user()->role === 'travels')
                            {{ $delegate->user->passport_number ?? '-' }}
                        @else
                            <span class="text-gray-400">••••••••</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if(auth('admin')->user()->role === 'travels')
                            {{ $delegate->user->airport_of_origin ?? '-' }}
                        @else
                            <span class="text-gray-400">••••••••</span>
                        @endif
                    </td>
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
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                        No approved delegates found
                        @if(request()->hasAny(['search', 'delegate_category', 'country']))
                            matching your filters
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-6">
        {{ $delegates->links() }}
    </div>
</div>

<!-- Include PDF Preview Modal -->
@include('components.invitation-preview-modal')

@endsection

