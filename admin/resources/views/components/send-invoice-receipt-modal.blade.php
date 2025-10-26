<!-- Send Invoice Receipt Modal -->
<div id="sendInvoiceReceiptModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-6/12"
 style="display: none; z-index: 10000; position:absolute; background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-2xl rounded-lg bg-white"
     style="max-width: 50%; margin:0 auto; padding:10px; top:10%; ">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-paper-plane text-green-500 mr-2"></i>
                    Send Receipt
                </h3>
                <button type="button" onclick="closeSendInvoiceReceiptModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="sendInvoiceReceiptForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <label for="send_invoice_receipt_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Recipient Email <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="send_invoice_receipt_email" 
                        name="email" 
                        required
                        placeholder="recipient@example.com"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                </div>

                <div class="bg-green-50 border-l-4 border-green-400 p-3 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                The receipt will be generated as a PDF and attached to the email.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="closeSendInvoiceReceiptModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors duration-200"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center"
                    >
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openSendInvoiceReceiptModal(invoiceId, email = '') {
    const modal = document.getElementById('sendInvoiceReceiptModal');
    const form = document.getElementById('sendInvoiceReceiptForm');
    const emailInput = document.getElementById('send_invoice_receipt_email');
    
    if (!modal || !form || !emailInput) {
        console.error('Send Invoice Receipt modal elements not found');
        return;
    }
    
    // Set form action
    form.action = `{{ url('/invoices') }}/${invoiceId}/receipt/send`;
    
    // Pre-fill email if provided
    emailInput.value = email || '';
    
    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Focus
    setTimeout(() => emailInput.focus(), 100);
}

function closeSendInvoiceReceiptModal() {
    const modal = document.getElementById('sendInvoiceReceiptModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Delegate click for send buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.send-invoice-receipt-btn')) {
        const btn = e.target.closest('.send-invoice-receipt-btn');
        const invoiceId = btn.getAttribute('data-invoice-id');
        const email = btn.getAttribute('data-email') || '';
        openSendInvoiceReceiptModal(invoiceId, email);
    }
});

// ESC close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSendInvoiceReceiptModal();
});

// Close on backdrop click
document.getElementById('sendInvoiceReceiptModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSendInvoiceReceiptModal();
    }
});
</script>