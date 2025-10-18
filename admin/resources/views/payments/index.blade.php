@extends('layouts.app')

@section('title', 'Payments')
@section('page-title', 'Payment Transactions')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">All Payments</h3>
            
            <form method="GET" action="{{ route('payments.export') }}">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if(request('package_id'))
                    <input type="hidden" name="package_id" value="{{ request('package_id') }}">
                @endif
                @if(request('country'))
                    <input type="hidden" name="country" value="{{ request('country') }}">
                @endif
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
            </form>
        </div>

        <!-- Filter Form - Always Visible -->
        <!-- Responsive Filter Form -->
        <form method="GET" class="bg-gray-50 p-4 rounded-lg">
            <!-- Mobile: Stack vertically, Desktop: Grid layout -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <!-- Search Field -->
                <div class="sm:col-span-2 lg:col-span-1">
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

                <!-- Package Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-box mr-1"></i>Package
                    </label>
                    <select 
                        name="package_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-globe mr-1"></i>Country
                    </label>
                    <select 
                        name="country" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
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

            <!-- Action Buttons - Responsive Layout -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-2">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-search mr-2"></i>Apply Filters
                </button>
                <a href="{{ route('payments.index') }}" class="flex-1 sm:flex-none px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-times mr-2"></i>Clear Filters
                </a>
                <a href="{{ route('payments.export', request()->query()) }}" class="flex-1 sm:flex-none px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </a>
            </div>
        </form>

        <!-- Filter Summary -->
        @if(request()->hasAny(['search', 'package_id', 'country']))
        <div class="mt-4 flex flex-wrap gap-2 mt-2">
            <span class="text-sm text-gray-600">Active filters:</span>
            @if(request('search'))
                <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Search: "{{ request('search') }}"
                </span>
            @endif
            @if(request('package_id'))
                @php
                    $selectedPackage = $packages->find(request('package_id'));
                @endphp
                @if($selectedPackage)
                    <span class="inline-flex items-center px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">
                        Package: {{ $selectedPackage->name }}
                    </span>
                @endif
            @endif
            @if(request('country'))
                <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    Country: {{ request('country') }}
                </span>
            @endif
        </div>
        @endif

        <!-- Statistics Summary -->
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-2">
                <div class="flex items-center py-2">
                    <div class="bg-blue-100 rounded-full p-3 mr-3">
                        <i class="fas fa-dollar-sign text-xl text-blue-600"></i>
                    </div>
                    <div class="py-2">
                        <p class="text-sm text-gray-600">Total Payments</p>
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($totalPaymentAmount, 2) }}</p>
                    </div>
                </div>
            </div>

            @if(request('package_id'))
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="bg-purple-100 rounded-full p-3 mr-3">
                        <i class="fas fa-box text-xl text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Filtered Package</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $packages->find(request('package_id'))->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if(request('country'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full p-3 mr-3">
                        <i class="fas fa-globe text-xl text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Filtered Country</p>
                        <p class="text-lg font-semibold text-gray-900">{{ request('country') }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Showing records info and per-page selector -->
    <div class="mb-4 mt-2 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <p class="text-sm text-gray-700 leading-5">
            Showing
            @if ($payments->firstItem())
                <span class="font-medium">{{ $payments->firstItem() }}</span>
                to
                <span class="font-medium">{{ $payments->lastItem() }}</span>
            @else
                {{ $payments->count() }}
            @endif
            of
            <span class="font-medium">{{ $payments->total() }}</span>
            payments
        </p>
        
        <!-- Per-page selector -->
        <x-per-page-selector :paginator="$payments" :current-per-page="request('per_page', 50)" />
    </div>

    <div class="table-container">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
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
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'country', 'direction' => request('sort') == 'country' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Country</span>
                            @if(request('sort') == 'country')
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Passport No.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Airport of Origin</th>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'payment_completed_at', 'direction' => request('sort') == 'payment_completed_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Date</span>
                            @if(request('sort') == 'payment_completed_at')
                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($payments as $index => $payment)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $payments->firstItem() + $index }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                        {{ $payment->user->full_name }}
                        
                        
                        @if(in_array(auth('admin')->user()->role, ['admin', 'secretariat']) && $payment->registration_type !== 'individual')
                        <small><a href="{{ route('registration-participants.index', $payment) }}" 
                           class="ml-3 text-red-600 hover:text-red-900"
                           title="View Participants">
                            <i class="fas fa-users"></i> Participants
                        </a></small>
                        @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $payment->user->email }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $payment->user->country ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $payment->package->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if(in_array(auth('admin')->user()->role, ['admin', 'travels']))
                            {{ $payment->user->passport_number ?? '-' }}
                        @else
                            <span class="text-gray-400">••••••••</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if(in_array(auth('admin')->user()->role, ['admin', 'travels']))
                            {{ $payment->user->airport_of_origin ?? '-' }}
                        @else
                            <span class="text-gray-400">••••••••</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                        ${{ number_format($payment->total_amount ?? $payment->payment->amount ?? 0, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $payment->payment ? ucfirst(str_replace('_', ' ', $payment->payment->payment_method)) : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $payment->payment && $payment->payment->payment_date ? $payment->payment->payment_date->format('M d, Y H:i') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('payments.show', $payment) }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="px-6 py-4 text-center text-gray-500">No payments found</td>
                </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </div>

    <div class="p-6">
        {{ $payments->appends(request()->query())->links() }}
    </div>
</div>
@endsection

