@extends('layouts.app')

@section('title', 'Participants')
@section('page-title', 'Participants')

@section('content')
<div class="space-y-6">
   
    <!-- Filters and Export -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <form method="GET" action="{{ route('participants.index') }}" class="space-y-4">
                <div class="flex flex-wrap items-end gap-4">
                    <!-- Search -->
                    <div class="flex-1 min-w-64">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input 
                            type="text" 
                            id="search" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Name or email..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <!-- Package Filter -->
                    <div class="flex-1 min-w-48">
                        <label for="package_id" class="block text-sm font-medium text-gray-700 mb-1">Package</label>
                        <select 
                            id="package_id" 
                            name="package_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All Packages</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ request('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Country Filter -->
                    <div class="flex-1 min-w-48">
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                        <select 
                            id="country" 
                            name="country" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All Countries</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                                    {{ $country }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    
                </div>

                <div class="flex-1 min-w-48 mt-2">
                <!-- Filter Buttons -->
                    <div class="flex items-end space-x-2">
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                        <a 
                            href="{{ route('participants.index') }}" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            <i class="fas fa-times mr-1"></i> Clear
                        </a>
                    </div>
                </div>

                <!-- Export Button -->
                <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <div class="flex items-center space-x-4">
                        @if(request()->hasAny(['search', 'package_id', 'country']))
                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                <i class="fas fa-filter"></i>
                                <span>Filtered by: </span>
                                @if(request('search'))
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                        Search: "{{ request('search') }}"
                                    </span>
                                @endif
                                @if(request('package_id'))
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                        Package: {{ $packages->find(request('package_id'))->name ?? 'Unknown' }}
                                    </span>
                                @endif
                                @if(request('country'))
                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">
                                        Country: {{ request('country') }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                    
                    <a 
                        href="{{ route('participants.export', request()->query()) }}" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        <i class="fas fa-download mr-2"></i> Export CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Participants Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mt-2">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">All Participants</h3>
            <p class="text-sm text-gray-500 mt-1">
                Showing {{ $registrations->firstItem() ?? 0 }} to {{ $registrations->lastItem() ?? 0 }} of {{ $registrations->total() }} registrations
                <span class="text-gray-700 font-medium">({{ $totalParticipants }} total people including group members)</span>
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Country</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package</th>
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delegate Category</th>
                        @if(!in_array(auth('admin')->user()->role, ['executive']))
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Registered</th>
                        @if(in_array(auth('admin')->user()->role, ['admin', 'hosts']))
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($registrations as $registration)
                    {{-- Primary Registrant --}}
                    @php
                        $isDelegate = $registration->status === 'approved';
                        $type = $isDelegate ? 'Delegate' : 'Paid Participant';
                    @endphp
                    {{-- Primary Registrant Row --}}
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            #{{ $registration->id }}
                            @if($registration->participants->count() > 0)
                                <span class="ml-1 p-1 text-xs font-semibold rounded bg-green-100 text-green-800" title="Group registration">
                                    +{{ $registration->participants->count() }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $registration->user->full_name ?? '-' }}
                            <span class="ml-1 text-xs text-gray-500">(Primary)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $registration->user->email ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $registration->user->phone ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $registration->user->country ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $registration->package->name ?? '-' }}
                            </span>
                        </td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $registration->user->delegate_category ?? '-' }}
                        </td>
                        @if(!in_array(auth('admin')->user()->role, ['executive']))
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($registration->status === 'approved')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle"></i> Approved
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ ucfirst($registration->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($registration->payment_status === 'completed')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle"></i> Paid
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    {{ ucfirst($registration->payment_status) }}
                                </span>
                            @endif
                        </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                <i class="fas fa-user-tie"></i> {{ $type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $registration->created_at ? $registration->created_at->format('M d, Y') : '-' }}
                        </td>
                        @if(in_array(auth('admin')->user()->role, ['admin', 'hosts']))
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('registrations.show', $registration) }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </td>
                        @endif
                    </tr>
                    
                    {{-- Additional Group Members --}}
                    @foreach($registration->participants as $groupMember)
                    <tr class="hover:bg-gray-50 bg-gray-25">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                            <i class="fas fa-arrow-turn-down-right ml-2"></i> #{{ $registration->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <span class="ml-4">{{ $groupMember->full_name }}</span>
                            <span class="ml-1 text-xs text-gray-500">(Member)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $groupMember->email ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            -
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $groupMember->nationality ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $registration->package->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $groupMember->delegate_category ?? '-' }}
                        </td>
                        @if(!in_array(auth('admin')->user()->role, ['executive']))
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                Group Member
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($registration->payment_status === 'completed')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle"></i> Paid
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    {{ ucfirst($registration->payment_status) }}
                                </span>
                            @endif
                        </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                <i class="fas fa-users"></i> Group Member
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $registration->created_at ? $registration->created_at->format('M d, Y') : '-' }}
                        </td>
                        @if(in_array(auth('admin')->user()->role, ['admin', 'hosts']))
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('registrations.show', $registration) }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                    @empty
                    <tr>
                        @php
                            $colspan = 9; // Base columns
                            if (!in_array(auth('admin')->user()->role, ['executive'])) {
                                $colspan += 2; // Status and Payment columns
                            }
                            if (in_array(auth('admin')->user()->role, ['admin', 'hosts'])) {
                                $colspan += 1; // Actions column
                            }
                        @endphp
                        <td colspan="{{ $colspan }}" class="px-6 py-4 text-center text-gray-500">
                            No participants found
                            @if(request()->hasAny(['search', 'package_id', 'country']))
                                matching your filters
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-6">
            {{ $registrations->appends(request()->query())->links('vendor.pagination.always-show-numbers') }}
        </div>
    </div>
</div>
@endsection
