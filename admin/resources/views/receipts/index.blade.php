@extends('layouts.app')

@section('title', 'Receipts')
@section('page-title', 'Receipt Management')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Receipt Management</h3>
            
            <div class="flex space-x-2">
                <a href="{{ route('receipts.export', request()->query()) }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center space-x-2">
                    <i class="fas fa-download"></i>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>

        <!-- Responsive Filter Form -->
        <form method="GET" class="bg-gray-50 p-4 rounded-lg mb-4">
            <!-- Mobile: Stack vertically, Desktop: Grid layout -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <!-- Search Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1"></i>Search
                    </label>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Participant name or email..." 
                        value="{{ request('search') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                    >
                </div>

                <!-- Registration Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-users mr-1"></i>Registration Type
                    </label>
                    <select 
                        name="registration_type" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                    >
                        <option value="">All Types</option>
                        <option value="individual" {{ request('registration_type') == 'individual' ? 'selected' : '' }}>Individual</option>
                        <option value="group" {{ request('registration_type') == 'group' ? 'selected' : '' }}>Group</option>
                    </select>
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="flex flex-col sm:flex-row gap-2">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center">
                    <i class="fas fa-filter mr-2"></i>Apply Filters
                </button>
                <a href="{{ route('receipts.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 flex items-center justify-center">
                    <i class="fas fa-times mr-2"></i>Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Receipts Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Receipt #</span>
                            @if(request('sort') == 'id')
                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'participant_name', 'direction' => request('sort') == 'participant_name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Participant</span>
                            @if(request('sort') == 'participant_name')
                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Package</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'total_amount', 'direction' => request('sort') == 'total_amount' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Amount</span>
                            @if(request('sort') == 'total_amount')
                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Payment Date</span>
                            @if(request('sort') == 'created_at')
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
                @forelse($receipts as $index => $receipt)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        RCP-{{ str_pad($receipt->id, 6, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $receipt->user->first_name }} {{ $receipt->user->last_name }}</div>
                        <div class="text-sm text-gray-500">{{ $receipt->user->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $receipt->package->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${{ number_format($receipt->total_amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $receipt->registration_type === 'individual' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ ucfirst($receipt->registration_type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $receipt->payment_completed_at ? $receipt->payment_completed_at->format('M d, Y') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('receipts.show', $receipt) }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i> View
                        </a>
                        
                        <button type="button" 
                                onclick="openReceiptModal({{ $receipt->id }})" 
                                class="ml-3 text-purple-600 hover:text-purple-900"
                                title="Preview Receipt PDF">
                            <i class="fas fa-eye"></i> PDF
                        </button>
                        
                        <a href="{{ route('receipts.download', $receipt) }}" class="ml-3 text-green-600 hover:text-green-900">
                            <i class="fas fa-download"></i> Download
                        </a>

                        <button type="button" 
                                class="ml-3 text-blue-600 hover:text-blue-900 send-receipt-btn"
                                data-receipt-id="{{ $receipt->id }}"
                                data-email="{{ $receipt->user->email }}">
                            <i class="fas fa-paper-plane"></i> Send
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        <div class="flex flex-col items-center py-8">
                            <i class="fas fa-receipt text-4xl text-gray-300 mb-4"></i>
                            <p class="text-lg font-medium text-gray-500 mb-2">No receipts found</p>
                            <p class="text-sm text-gray-400">No paid registrations available for receipt generation.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($receipts->hasPages())
    <div class="px-6 py-4 border-t">
        {{ $receipts->links() }}
    </div>
    @endif
</div>

<!-- Include Receipt Preview Modal -->
@include('components.receipt-preview-modal')

<!-- Include Send Receipt Modal -->
@include('components.send-receipt-modal')

@endsection
