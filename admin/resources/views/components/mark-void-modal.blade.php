<!-- Mark as Void Modal -->
<div id="markVoidModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-6/12"
 style="display: none; z-index: 10000; position:absolute; background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-2xl rounded-lg bg-white"
     style="max-width: 50%; margin:0 auto; padding:10px; top:10%; ">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-ban text-red-500 mr-2"></i>
                    Void Registration
                </h3>
                <button type="button" onclick="closeMarkVoidModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="markVoidForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-4" id="voidModalMessage">
                        You are about to void the registration for:
                    </p>
                    <p class="text-sm font-semibold text-gray-900 mb-4" id="voidRegistrantName"></p>
                    
                    <!-- Hidden input for multiple registration IDs -->
                    <div id="voidRegistrationIdsContainer"></div>
                    
                    <!-- Void Reason -->
                    <label for="void_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for Voiding <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="void_reason" 
                        name="void_reason" 
                        rows="4" 
                        required
                        maxlength="1000"
                        placeholder="Enter reason for voiding this registration..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle"></i> Please provide a detailed reason for voiding this registration.
                    </p>
                </div>

                <div class="bg-red-50 border-l-4 border-red-400 p-3 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <strong>Warning:</strong> Voiding a registration will mark it as cancelled and cannot be easily undone. The registration will be excluded from all reports and lists.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="closeMarkVoidModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 ml-2 bg-red-700 text-white rounded-lg hover:bg-red-800"
                    >
                        <i class="fas fa-ban"></i> Confirm & Void Registration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Mark as Void Modal Functions (global scope for onclick handlers)
function openMarkVoidModal(registrationId, registrantName) {
    const modal = document.getElementById('markVoidModal');
    const form = document.getElementById('markVoidForm');
    const nameElement = document.getElementById('voidRegistrantName');
    const messageElement = document.getElementById('voidModalMessage');
    const reasonInput = document.getElementById('void_reason');
    const idsContainer = document.getElementById('voidRegistrationIdsContainer');
    
    if (!modal || !form || !nameElement) {
        console.error('Mark Void modal elements not found');
        return;
    }
    
    // Set form action
    form.action = `{{ url('registrations') }}/${registrationId}/void`;
    
    // Set registrant name
    messageElement.textContent = 'You are about to void the registration for:';
    nameElement.textContent = registrantName;
    nameElement.style.display = 'block';
    
    // Clear hidden inputs
    idsContainer.innerHTML = '';
    
    // Clear previous reason
    reasonInput.value = '';
    
    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Focus on reason field
    setTimeout(() => reasonInput.focus(), 100);
}

// Bulk void modal function
function openBulkVoidModal(registrationIds) {
    const modal = document.getElementById('markVoidModal');
    const form = document.getElementById('markVoidForm');
    const nameElement = document.getElementById('voidRegistrantName');
    const messageElement = document.getElementById('voidModalMessage');
    const reasonInput = document.getElementById('void_reason');
    const idsContainer = document.getElementById('voidRegistrationIdsContainer');
    
    if (!modal || !form) {
        console.error('Mark Void modal elements not found');
        return;
    }
    
    // Set form action to a generic void endpoint
    form.action = `{{ url('registrations/void-bulk') }}`;
    
    // Update message for bulk void
    messageElement.textContent = `You are about to void ${registrationIds.length} registration(s):`;
    nameElement.style.display = 'none';
    
    // Clear and add hidden inputs for registration IDs
    idsContainer.innerHTML = '';
    registrationIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'registration_ids[]';
        input.value = id;
        idsContainer.appendChild(input);
    });
    
    // Clear previous reason
    reasonInput.value = '';
    
    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Focus on reason field
    setTimeout(() => reasonInput.focus(), 100);
}

function closeMarkVoidModal() {
    const modal = document.getElementById('markVoidModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMarkVoidModal();
    }
});

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('markVoidModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeMarkVoidModal();
            }
        });
    }
});
</script>

