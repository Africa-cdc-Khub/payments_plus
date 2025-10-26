<!-- Reject Delegate Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-6/12"
 style="display: none; z-index: 10000; position:absolute; background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-2xl rounded-lg bg-white"
     style="max-width: 30%; margin:0 auto; padding:10px; top:10%;">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-times-circle text-red-500 mr-2"></i>
                    Reject Delegate Registration
                </h3>
                <button type="button" onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="rejectForm" method="POST" action="">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-4">
                        You are about to reject the delegate registration for:
                    </p>
                    <p class="text-sm font-semibold text-gray-900 mb-4" id="delegateName"></p>
                    
                    <!-- Rejection Reason -->
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Rejection Reason <span class="text-gray-500">(Optional)</span>
                    </label>
                    <textarea 
                        name="reason" 
                        id="rejection_reason" 
                        rows="4" 
                        maxlength="500"
                        placeholder="Provide a reason for rejection (optional)..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle"></i> This reason will be included in the rejection email sent to the delegate.
                    </p>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                This action will reject the delegate registration and send a notification email. The delegate will be informed about re-registration options.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="closeRejectModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 ml-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                        style="background-color: red;"
                    >
                        <i class="fas fa-times-circle"></i> Confirm & Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Reject Delegate Modal Functions (global scope for onclick handlers)
function openRejectModal(delegateId, delegateName) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    const nameElement = document.getElementById('delegateName');
    const reasonTextarea = document.getElementById('rejection_reason');
    
    if (!modal || !form || !nameElement) {
        console.error('Reject modal elements not found');
        return;
    }
    
    // Set form action
    form.action = `<?php echo e(url('delegates')); ?>/${delegateId}/reject`;
    
    // Set delegate name
    nameElement.textContent = delegateName;
    
    // Clear previous reason
    reasonTextarea.value = '';
    
    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Focus on reason field
    setTimeout(() => reasonTextarea.focus(), 100);
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRejectModal();
    }
});

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('rejectModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
    }
});
</script>


<?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/components/reject-delegate-modal.blade.php ENDPATH**/ ?>