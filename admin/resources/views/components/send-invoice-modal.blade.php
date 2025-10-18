<!-- Send Invoice Modal -->
<div id="sendInvoiceModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-6/12"
 style="display: none; z-index: 10000; position:absolute; background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-2xl rounded-lg bg-white"
     style="max-width: 50%; margin:0 auto; padding:10px; top:10%; ">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-paper-plane text-blue-500 mr-2"></i>
                    Send Invoice
                </h3>
                <button type="button" onclick="closeSendInvoiceModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="sendInvoiceForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <label for="send_invoice_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Recipient Email <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="send_invoice_email" 
                        name="email" 
                        required
                        placeholder="recipient@example.com"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                The invoice PDF will be generated and attached to the email.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="closeSendInvoiceModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 ml-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        <i class="fas fa-paper-plane"></i> Send
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openSendInvoiceModal(invoiceId, email = '') {
    const modal = document.getElementById('sendInvoiceModal');
    const form = document.getElementById('sendInvoiceForm');
    const emailInput = document.getElementById('send_invoice_email');
    
    if (!modal || !form || !emailInput) {
        console.error('Send Invoice modal elements not found');
        return;
    }
    
    // Set form action
    form.action = `{{ url('/invoices') }}/${invoiceId}/send`;
    
    // Pre-fill email if provided
    emailInput.value = email || '';
    
    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Focus
    setTimeout(() => emailInput.focus(), 100);
}

function closeSendInvoiceModal() {
    const modal = document.getElementById('sendInvoiceModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Delegate click for send buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.send-invoice-btn')) {
        const btn = e.target.closest('.send-invoice-btn');
        const invoiceId = btn.getAttribute('data-invoice-id');
        const email = btn.getAttribute('data-email') || '';
        openSendInvoiceModal(invoiceId, email);
    }
});

// ESC close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSendInvoiceModal();
});

// Click outside to close
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('sendInvoiceModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeSendInvoiceModal();
        });
    }
});
</script>


