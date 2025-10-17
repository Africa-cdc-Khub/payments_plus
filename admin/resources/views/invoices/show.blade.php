@extends('layouts.app')

@section('title', 'Invoice Details')
@section('page-title', 'Invoice #' . $invoice->invoice_number)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Invoice Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Invoice Information</h3>
        <dl class="space-y-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                <dd class="text-sm text-gray-900">{{ $invoice->invoice_number }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="text-sm">
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
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Amount</dt>
                <dd class="text-sm text-gray-900">{{ $invoice->formatted_amount }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Item</dt>
                <dd class="text-sm text-gray-900">{{ $invoice->item }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Description</dt>
                <dd class="text-sm text-gray-900">{{ $invoice->description }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                <dd class="text-sm text-gray-900">{{ $invoice->created_at->format('M d, Y H:i') }}</dd>
            </div>
            @if($invoice->paid_at)
            <div>
                <dt class="text-sm font-medium text-gray-500">Paid Date</dt>
                <dd class="text-sm text-gray-900">{{ $invoice->paid_at->format('M d, Y H:i') }}</dd>
            </div>
            @endif
            @if($invoice->cancelled_at)
            <div>
                <dt class="text-sm font-medium text-gray-500">Cancelled Date</dt>
                <dd class="text-sm text-gray-900">{{ $invoice->cancelled_at->format('M d, Y H:i') }}</dd>
            </div>
            @endif
        </dl>
    </div>

    <!-- Biller Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Biller Information</h3>
        <dl class="space-y-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">Name</dt>
                <dd class="text-sm text-gray-900">{{ $invoice->biller_name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="text-sm text-gray-900">{{ $invoice->biller_email }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Address</dt>
                <dd class="text-sm text-gray-900 whitespace-pre-line">{{ $invoice->biller_address }}</dd>
            </div>
        </dl>
    </div>
</div>

<!-- Actions -->
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Actions</h3>
    <div class="flex space-x-4">
        <button type="button" 
                onclick="openInvoiceModal({{ $invoice->id }})" 
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            <i class="fas fa-eye"></i> Preview Invoice
        </button>
        
        <a href="{{ route('invoices.download', $invoice) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            <i class="fas fa-download"></i> Download Invoice
        </a>

        <button type="button"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 send-invoice-btn"
                data-invoice-id="{{ $invoice->id }}"
                data-email="{{ $invoice->biller_email }}">
            <i class="fas fa-paper-plane"></i> Send Invoice
        </button>
        
        @if($invoice->status === 'pending')
            <button type="button" 
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 edit-invoice-btn"
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
                <i class="fas fa-edit"></i> Edit Invoice
            </button>
            
            <button type="button" 
                    onclick="markAsPaid({{ $invoice->id }}, '{{ addslashes($invoice->biller_name) }}')" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-check-circle"></i> Mark as Paid
            </button>
            
            <button type="button" 
                    onclick="cancelInvoice({{ $invoice->id }}, '{{ addslashes($invoice->biller_name) }}')" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-times-circle"></i> Cancel Invoice
            </button>
        @endif
        
        <a href="{{ route('invoices.index') }}" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
            <i class="fas fa-arrow-left"></i> Back to Invoices
        </a>
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

<!-- Include Edit Invoice Modal -->
@include('components.edit-invoice-modal')

<!-- Include Send Invoice Modal -->
@include('components.send-invoice-modal')

@endsection
