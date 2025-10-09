<!-- Mark Travel Processed Modal -->
<div id="markTravelProcessedModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-6/12"
 style="display: none; z-index: 10000; position:absolute; background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-2xl rounded-lg bg-white"
     style="max-width: 20%; margin:0 auto; padding:10px; top:10%;">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-plane text-blue-500 mr-2"></i>
                    <span id="modalTitle">Mark Travel as Processed</span>
                </h3>
                <button type="button" onclick="closeTravelProcessedModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="markTravelProcessedForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-4" id="modalMessage">
                        You are about to mark travel arrangements as processed for:
                    </p>
                    <p class="text-sm font-semibold text-gray-900 mb-4" id="delegateName"></p>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-4" id="processingInfo">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700" id="infoText">
                                    This will mark the delegate's travel arrangements as processed. You can unmark this later if needed.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4" id="unprocessingWarning" style="display: none;">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    This will unmark the delegate's travel as processed and move them back to pending status.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="closeTravelProcessedModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        id="confirmButton"
                        class="px-4 py-2 ml-2 bg-green-700 text-white rounded-lg "
                        
                        style="background-color: green;"
                    >
                        <i class="fas fa-check"></i> <span id="confirmButtonText">Confirm & Mark Processed</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Mark Travel Processed Modal Functions (global scope for onclick handlers)
function openTravelProcessedModal(registrationId, delegateName, isProcessed) {
    const modal = document.getElementById('markTravelProcessedModal');
    const form = document.getElementById('markTravelProcessedForm');
    const nameElement = document.getElementById('delegateName');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const confirmButton = document.getElementById('confirmButton');
    const confirmButtonText = document.getElementById('confirmButtonText');
    const processingInfo = document.getElementById('processingInfo');
    const unprocessingWarning = document.getElementById('unprocessingWarning');
    
    if (!modal || !form || !nameElement) {
        console.error('Travel Processed modal elements not found');
        return;
    }
    
    // Set form action
    form.action = `/approved-delegates/${registrationId}/mark-processed`;
    
    // Set delegate name
    nameElement.textContent = delegateName;
    
    // Update modal content based on current status
    if (isProcessed) {
        // Unmark mode
        modalTitle.textContent = 'Unmark Travel as Processed';
        modalMessage.textContent = 'You are about to unmark travel arrangements for:';
        confirmButtonText.textContent = 'Confirm & Unmark';
        confirmButton.className = 'px-4 py-2 ml-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700';
        confirmButton.innerHTML = '<i class="fas fa-undo"></i> <span id="confirmButtonText">Confirm & Unmark</span>';
        processingInfo.style.display = 'none';
        unprocessingWarning.style.display = 'block';
    } else {
        // Mark mode
        modalTitle.textContent = 'Mark Travel as Processed';
        modalMessage.textContent = 'You are about to mark travel arrangements as processed for:';
        confirmButtonText.textContent = 'Confirm & Mark Processed';
        confirmButton.className = 'px-4 py-2 ml-2 bg-green-700 text-white rounded-lg hover:bg-green-800';
        confirmButton.innerHTML = '<i class="fas fa-check"></i> <span id="confirmButtonText">Confirm & Mark Processed</span>';
        processingInfo.style.display = 'block';
        unprocessingWarning.style.display = 'none';
    }
    
    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeTravelProcessedModal() {
    const modal = document.getElementById('markTravelProcessedModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTravelProcessedModal();
    }
});

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('markTravelProcessedModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeTravelProcessedModal();
            }
        });
    }
});
</script>


