<!-- Mark as Paid Modal -->
<div id="markPaidModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-6/12"
 style="display: none; z-index: 10000; position:absolute; background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-2xl rounded-lg bg-white"
     style="max-width: 50%; margin:0 auto; padding:10px; top:10%; ">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-money-bill-wave text-orange-500 mr-2"></i>
                    Mark Payment as Paid
                </h3>
                <button type="button" onclick="closeMarkPaidModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="markPaidForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-4">
                        You are about to manually mark the payment as paid for:
                    </p>
                    <p class="text-sm font-semibold text-gray-900 mb-4" id="registrantName"></p>
                    
                    <!-- Amount Paid -->
                    <div class="mb-4">
                        <label for="amount_paid" class="block text-sm font-medium text-gray-700 mb-2">
                            Amount Paid (USD) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="amount_paid" 
                            name="amount_paid" 
                            step="0.01"
                            min="0"
                            required
                            placeholder="0.00"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                        >
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-4">
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                            Payment Method <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="payment_method" 
                            name="payment_method" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                        >
                            <option value="">Select Payment Method</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="online">Online Payment</option>
                        </select>
                    </div>
                    
                    <!-- Remarks -->
                    <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">
                        Remarks <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="remarks" 
                        name="remarks" 
                        rows="4" 
                        required
                        maxlength="1000"
                        placeholder="Enter payment details, reference number, or any relevant notes..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle"></i> Please provide details about how this payment was received (e.g., bank reference, transaction ID, etc.)
                    </p>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                This action will mark the registration as paid and cannot be easily undone. Make sure all payment details are verified.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="closeMarkPaidModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 ml-2 bg-green-700 text-white rounded-lg"
                        style="background-color: green;"
                    >
                        <i class="fas fa-check"></i> Confirm & Mark Paid
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Mark as Paid Modal Functions (global scope for onclick handlers)
function openMarkPaidModal(registrationId, registrantName, registrationAmount = '') {
    const modal = document.getElementById('markPaidModal');
    const form = document.getElementById('markPaidForm');
    const nameElement = document.getElementById('registrantName');
    const amountInput = document.getElementById('amount_paid');
    const paymentMethodSelect = document.getElementById('payment_method');
    const remarksInput = document.getElementById('remarks');
    
    if (!modal || !form || !nameElement) {
        console.error('Mark Paid modal elements not found');
        return;
    }
    
    // Set form action
    form.action = `/registrations/${registrationId}/mark-paid`;
    
    // Set registrant name
    nameElement.textContent = registrantName;
    
    // Pre-fill amount if provided, otherwise clear
    amountInput.value = registrationAmount || '';
    
    // Reset payment method
    paymentMethodSelect.value = '';
    
    // Clear previous remarks
    remarksInput.value = '';
    
    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Focus on amount field
    setTimeout(() => amountInput.focus(), 100);
}

function closeMarkPaidModal() {
    const modal = document.getElementById('markPaidModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMarkPaidModal();
    }
});

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('markPaidModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeMarkPaidModal();
            }
        });
    }
});
</script>

