@extends('layouts.app')

@section('title', 'Invoices')
@section('page-title', 'Invoice Management')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Invoice Management</h3>
            
            <div class="flex space-x-2">
                <a href="{{ route('invoices.export', request()->query()) }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center space-x-2">
                    <i class="fas fa-download"></i>
                    <span>Export CSV</span>
                </a>
                <button type="button" onclick="openCreateInvoiceModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus"></i> Create Invoice
                </button>
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
                        placeholder="Invoice number, biller name or email..." 
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
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons - Responsive Layout -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-2">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-search mr-2"></i>Apply Filters
                </button>
                <a href="{{ route('invoices.index') }}" class="flex-1 sm:flex-none px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-times mr-2"></i>Clear Filters
                </a>
                <a href="{{ route('invoices.export', request()->query()) }}" class="flex-1 sm:flex-none px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </a>
            </div>
        </form>

        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-clock text-yellow-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-yellow-900">Pending Invoices</h4>
                        <p class="text-2xl font-bold text-yellow-700">{{ $invoices->where('status', 'pending')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-green-900">Paid Invoices</h4>
                        <p class="text-2xl font-bold text-green-700">{{ $invoices->where('status', 'paid')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-times-circle text-red-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-red-900">Cancelled Invoices</h4>
                        <p class="text-2xl font-bold text-red-700">{{ $invoices->where('status', 'cancelled')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Showing records info and per-page selector -->
    <div class="mb-4 mt-2 px-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <p class="text-sm text-gray-700 leading-5">
            Showing
            @if ($invoices->firstItem())
                <span class="font-medium">{{ $invoices->firstItem() }}</span>
                to
                <span class="font-medium">{{ $invoices->lastItem() }}</span>
            @else
                {{ $invoices->count() }}
            @endif
            of
            <span class="font-medium">{{ $invoices->total() }}</span>
            invoices
        </p>
        
        <!-- Per-page selector -->
        <x-per-page-selector :paginator="$invoices" :current-per-page="request('per_page', 50)" />
    </div>

    <div class="table-container">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'invoice_number', 'direction' => request('sort') == 'invoice_number' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Invoice #</span>
                            @if(request('sort') == 'invoice_number')
                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'biller_name', 'direction' => request('sort') == 'biller_name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Biller</span>
                            @if(request('sort') == 'biller_name')
                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Status</span>
                            @if(request('sort') == 'status')
                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Created</span>
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
                @forelse($invoices as $index => $invoice)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $invoices->firstItem() + $index }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $invoice->invoice_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $invoice->biller_name }}</div>
                        <div class="text-sm text-gray-500">{{ $invoice->biller_email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $invoice->item }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $invoice->formatted_amount }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($invoice->status === 'pending')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i>Pending
                            </span>
                        @elseif($invoice->status === 'paid')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i>Paid
                            </span>
                        @elseif($invoice->status === 'cancelled')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times mr-1"></i>Cancelled
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $invoice->created_at ? $invoice->created_at->format('M d, Y') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i> View
                        </a>
                        
                        <button type="button" 
                                onclick="openInvoiceModal({{ $invoice->id }})" 
                                class="ml-3 text-purple-600 hover:text-purple-900"
                                title="Preview PDF">
                            <i class="fas fa-eye"></i> PDF
                        </button>
                        
                        <a href="{{ route('invoices.email-preview', $invoice) }}" 
                           target="_blank"
                           class="ml-3 text-indigo-600 hover:text-indigo-900"
                           title="Preview Email">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                        
                        <a href="{{ route('invoices.download', $invoice) }}" class="ml-3 text-green-600 hover:text-green-900">
                            <i class="fas fa-download"></i> Download
                        </a>

                        <button type="button"
                                class="ml-3 text-blue-600 hover:text-blue-900 send-invoice-btn"
                                data-invoice-id="{{ $invoice->id }}"
                                data-email="{{ $invoice->biller_email }}">
                            <i class="fas fa-paper-plane"></i> Send
                        </button>
                        
                        @if($invoice->status === 'pending')
                            <button type="button" 
                                    class="ml-3 text-orange-600 hover:text-orange-900 edit-invoice-btn"
                                    title="Edit Invoice"
                                    data-invoice-id="{{ $invoice->id }}"
                                    data-biller-name="{{ $invoice->biller_name }}"
                                    data-biller-email="{{ $invoice->biller_email }}"
                                    data-biller-address="{{ $invoice->biller_address }}"
                                    data-item="{{ $invoice->item }}"
                                    data-description="{{ $invoice->description }}"
                                    data-quantity="{{ $invoice->quantity }}"
                                    data-rate="{{ $invoice->rate }}"
                                    data-currency="{{ $invoice->currency }}"
                                    data-status="{{ $invoice->status }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            
                            <button type="button" 
                                    onclick="markAsPaid({{ $invoice->id }}, '{{ addslashes($invoice->biller_name) }}')" 
                                    class="ml-3 text-green-600 hover:text-green-900"
                                    title="Mark as Paid">
                                <i class="fas fa-check-circle"></i> Mark Paid
                            </button>
                            
                            <button type="button" 
                                    onclick="cancelInvoice({{ $invoice->id }}, '{{ addslashes($invoice->biller_name) }}')" 
                                    class="ml-3 text-red-600 hover:text-red-900"
                                    title="Cancel Invoice">
                                <i class="fas fa-times-circle"></i> Cancel
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        No invoices found
                        @if(request()->hasAny(['search', 'status']))
                            matching your filters
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </div>

    <div class="p-6">
        {{ $invoices->appends(request()->query())->links() }}
    </div>
</div>

<script>
function markAsPaid(invoiceId, billerName) {
    if (confirm(`Are you sure you want to mark invoice for ${billerName} as paid?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('invoices') }}/${invoiceId}/mark-paid`;
        
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = '{{ csrf_token() }}';
        form.appendChild(token);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function cancelInvoice(invoiceId, billerName) {
    if (confirm(`Are you sure you want to cancel invoice for ${billerName}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('invoices') }}/${invoiceId}/cancel`;
        
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = '{{ csrf_token() }}';
        form.appendChild(token);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<!-- Include Invoice Preview Modal -->
@include('components.invoice-preview-modal')

<!-- Include Create Invoice Modal -->
@include('components.create-invoice-modal')

<!-- Include Edit Invoice Modal -->
@include('components.edit-invoice-modal')

<!-- Include Send Invoice Modal -->
@include('components.send-invoice-modal')

@endsection
