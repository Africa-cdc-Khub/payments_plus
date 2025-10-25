<!-- Create Invoice Modal -->
<div id="createInvoiceModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-6/12"
 style="display: none; z-index: 10000; position:absolute; background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-2xl rounded-lg bg-white"
     style="max-width: 60%; margin:0 auto; padding:10px; top:5%; ">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-file-invoice text-blue-500 mr-2"></i>
                    Create New Invoice
                </h3>
                <button type="button" onclick="closeCreateInvoiceModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="createInvoiceForm" method="POST" action="<?php echo e(route('invoices.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Biller Information -->
                    <div class="space-y-4">
                        <h4 class="text-md font-medium text-gray-800 border-b pb-2">Biller Information</h4>
                        
                        <div>
                            <label for="biller_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Biller Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="biller_name" 
                                name="biller_name" 
                                required
                                placeholder="Enter biller name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label for="biller_email" class="block text-sm font-medium text-gray-700 mb-2">
                                Biller Email <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="biller_email" 
                                name="biller_email" 
                                required
                                placeholder="Enter biller email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label for="biller_address" class="block text-sm font-medium text-gray-700 mb-2">
                                Biller Address <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="biller_address" 
                                name="biller_address" 
                                rows="3" 
                                required
                                placeholder="Enter complete billing address"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="space-y-4">
                        <h4 class="text-md font-medium text-gray-800 border-b pb-2">Invoice Details</h4>
                        
                        <div>
                            <label for="item" class="block text-sm font-medium text-gray-700 mb-2">
                                Item/Service <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="item" 
                                name="item" 
                                required
                                placeholder="e.g., Conference Registration"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="2" 
                                placeholder="Detailed description of the item/service"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                    Quantity <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    id="quantity" 
                                    name="quantity" 
                                    step="1"
                                    min="1"
                                    value="1"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                            </div>

                            <div>
                                <label for="rate" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rate (USD) <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    id="rate" 
                                    name="rate" 
                                    step="0.01"
                                    min="0"
                                    required
                                    placeholder="0.00"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                                    Currency <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    id="currency" 
                                    name="currency" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                                <div id="totalAmount" class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                                    $0.00
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                The invoice will be created with "Pending" status. You can download and send it to the biller, then mark it as paid when payment is received.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="closeCreateInvoiceModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 ml-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        <i class="fas fa-plus"></i> Create Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Create Invoice Modal Functions
function openCreateInvoiceModal() {
    const modal = document.getElementById('createInvoiceModal');
    const form = document.getElementById('createInvoiceForm');
    
    if (!modal || !form) {
        console.error('Create Invoice modal elements not found');
        return;
    }
    
    // Reset form
    form.reset();
    document.getElementById('quantity').value = '1';
    updateTotalAmount();
    
    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Focus on first input
    setTimeout(() => document.getElementById('biller_name').focus(), 100);
}

function closeCreateInvoiceModal() {
    const modal = document.getElementById('createInvoiceModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    }
}

function updateTotalAmount() {
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const rate = parseFloat(document.getElementById('rate').value) || 0;
    const currency = document.getElementById('currency').value;
    const total = quantity * rate;
    
    document.getElementById('totalAmount').textContent = currency + ' ' + total.toFixed(2);
}

// Event listeners for quantity and rate changes
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantity');
    const rateInput = document.getElementById('rate');
    
    if (quantityInput) {
        quantityInput.addEventListener('input', updateTotalAmount);
    }
    
    if (rateInput) {
        rateInput.addEventListener('input', updateTotalAmount);
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateInvoiceModal();
    }
});

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('createInvoiceModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateInvoiceModal();
            }
        });
    }
});
</script>
<?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/components/create-invoice-modal.blade.php ENDPATH**/ ?>