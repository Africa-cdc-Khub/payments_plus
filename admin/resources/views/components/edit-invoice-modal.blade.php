<!-- Edit Invoice Modal -->
<div id="editInvoiceModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-6/12"
 style="display: none; z-index: 10000; position:absolute; background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-2xl rounded-lg bg-white"
     style="max-width: 60%; margin:0 auto; padding:10px; top:5%; ">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-edit text-orange-500 mr-2"></i>
                    Edit Invoice
                </h3>
                <button type="button" onclick="closeEditInvoiceModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="editInvoiceForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Biller Information -->
                    <div class="space-y-4">
                        <h4 class="text-md font-medium text-gray-800 border-b pb-2">Biller Information</h4>
                        
                        <div>
                            <label for="edit_biller_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Biller Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="edit_biller_name" 
                                name="biller_name" 
                                required
                                placeholder="Enter biller name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                            >
                        </div>

                        <div>
                            <label for="edit_biller_email" class="block text-sm font-medium text-gray-700 mb-2">
                                Biller Email <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="edit_biller_email" 
                                name="biller_email" 
                                required
                                placeholder="Enter biller email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                            >
                        </div>

                        <div>
                            <label for="edit_biller_address" class="block text-sm font-medium text-gray-700 mb-2">
                                Biller Address <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="edit_biller_address" 
                                name="biller_address" 
                                rows="3" 
                                required
                                placeholder="Enter complete billing address"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="space-y-4">
                        <h4 class="text-md font-medium text-gray-800 border-b pb-2">Invoice Details</h4>
                        
                        <div>
                            <label for="edit_item" class="block text-sm font-medium text-gray-700 mb-2">
                                Item/Service <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="edit_item" 
                                name="item" 
                                required
                                placeholder="e.g., Conference Registration"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                            >
                        </div>

                        <div>
                            <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea 
                                id="edit_description" 
                                name="description" 
                                rows="2" 
                                placeholder="Detailed description of the item/service"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="edit_quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                    Quantity <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    id="edit_quantity" 
                                    name="quantity" 
                                    step="1"
                                    min="1"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                >
                            </div>

                            <div>
                                <label for="edit_rate" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rate (USD) <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    id="edit_rate" 
                                    name="rate" 
                                    step="0.01"
                                    min="0"
                                    required
                                    placeholder="0.00"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="edit_currency" class="block text-sm font-medium text-gray-700 mb-2">
                                    Currency <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    id="edit_currency" 
                                    name="currency" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                >
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="GBP">GBP</option>
                                    <option value="ZAR">ZAR</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Total Amount
                                </label>
                                <div id="editTotalAmount" class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                                    $0.00
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="edit_status" 
                                name="status" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                            >
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-orange-50 border-l-4 border-orange-400 p-3 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-orange-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-orange-700">
                                <strong>Note:</strong> Only pending invoices can be edited. Changing the status to "Paid" or "Cancelled" will lock the invoice from further edits.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="closeEditInvoiceModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 ml-2 text-white rounded-lg "
                        style="background-color: #007bff; border-color: #007bff;"
                    >
                        <i class="fas fa-save"></i> Update Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit Invoice Modal Functions
function openEditInvoiceModal(invoiceId, invoiceData) {
    const modal = document.getElementById('editInvoiceModal');
    const form = document.getElementById('editInvoiceForm');
    
    if (!modal || !form) {
        console.error('Edit Invoice modal elements not found');
        return;
    }
    
    // Set form action
    form.action = `{{ url('/invoices') }}/${invoiceId}`;
    
    // Populate form fields
    document.getElementById('edit_biller_name').value = invoiceData.biller_name || '';
    document.getElementById('edit_biller_email').value = invoiceData.biller_email || '';
    document.getElementById('edit_biller_address').value = invoiceData.biller_address || '';
    document.getElementById('edit_item').value = invoiceData.item || '';
    document.getElementById('edit_description').value = invoiceData.description || '';
    document.getElementById('edit_quantity').value = invoiceData.quantity || 1;
    document.getElementById('edit_rate').value = invoiceData.rate || 0;
    document.getElementById('edit_currency').value = invoiceData.currency || 'USD';
    document.getElementById('edit_status').value = invoiceData.status || 'pending';
    
    // Update total amount
    updateEditTotalAmount();
    
    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Focus on first input
    setTimeout(() => document.getElementById('edit_biller_name').focus(), 100);
}

function closeEditInvoiceModal() {
    const modal = document.getElementById('editInvoiceModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    }
}

function updateEditTotalAmount() {
    const quantity = parseFloat(document.getElementById('edit_quantity').value) || 0;
    const rate = parseFloat(document.getElementById('edit_rate').value) || 0;
    const currency = document.getElementById('edit_currency').value;
    const total = quantity * rate;
    
    document.getElementById('editTotalAmount').textContent = currency + ' ' + total.toFixed(2);
}

// Event delegation for edit buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.edit-invoice-btn')) {
        const button = e.target.closest('.edit-invoice-btn');
        const invoiceId = button.getAttribute('data-invoice-id');
        const invoiceData = {
            biller_name: button.getAttribute('data-biller-name'),
            biller_email: button.getAttribute('data-biller-email'),
            biller_address: button.getAttribute('data-biller-address'),
            item: button.getAttribute('data-item'),
            description: button.getAttribute('data-description'),
            quantity: parseInt(button.getAttribute('data-quantity')),
            rate: parseFloat(button.getAttribute('data-rate')),
            currency: button.getAttribute('data-currency'),
            status: button.getAttribute('data-status')
        };
        
        openEditInvoiceModal(invoiceId, invoiceData);
    }
});

// Event listeners for quantity and rate changes
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('edit_quantity');
    const rateInput = document.getElementById('edit_rate');
    
    if (quantityInput) {
        quantityInput.addEventListener('input', updateEditTotalAmount);
    }
    
    if (rateInput) {
        rateInput.addEventListener('input', updateEditTotalAmount);
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditInvoiceModal();
    }
});

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('editInvoiceModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditInvoiceModal();
            }
        });
    }
});
</script>
