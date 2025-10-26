<!-- Receipt PDF Preview Modal -->
<div id="receiptPreviewModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-6/12" style="display: none; z-index: 10000; position: fixed; top: 0; left: 0; right: 0; bottom: 0;  background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-5 mx-auto p-0 border w-full max-w-7xl shadow-2xl rounded-lg bg-white" style="height: 95vh;  max-width: 50%; margin: 0 auto;">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b bg-gray-50 rounded-t-lg py-2 px-2">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-file-invoice text-green-500 mr-2"></i>
                Receipt Preview
            </h3>
            <div class="flex items-center gap-2">
                <button type="button" onclick="downloadCurrentReceipt()" class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                    <i class="fas fa-download mr-1"></i> Download
                </button>
                <button type="button" onclick="closeReceiptModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none ml-2">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <!-- Modal Body with iframe -->
        <div class="relative bg-white" style="height: calc(95vh - 64px);">
            <div id="receiptLoader" class="absolute inset-0 flex items-center justify-center bg-gray-100 z-10" style="min-height: 90vh;">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-4xl text-green-600 mb-3"></i>
                    <p class="text-gray-600 text-lg">Generating Receipt PDF...</p>
                    <p class="text-gray-500 text-sm mt-2">This may take a few seconds</p>
                </div>
            </div>
            <iframe 
                id="receiptIframe" 
                name="receiptIframe"
                class="w-full h-full rounded-b-lg"
                style="border: none; background: white;  min-height: 90vh;">
            </iframe>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
let currentReceiptUrl = '';
let receiptLoadTimeout = null;

function openReceiptModal(registrationId) {
    const modal = document.getElementById('receiptPreviewModal');
    const iframe = document.getElementById('receiptIframe');
    const loader = document.getElementById('receiptLoader');
    
    if (!modal || !iframe || !loader) {
        console.error('Receipt modal elements not found');
        return;
    }
    
    // Reset iframe
    iframe.src = 'about:blank';
    
    // Show modal and loader
    modal.style.display = 'block';
    loader.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Store current receipt ID for download
    currentReceiptUrl = `/receipts/${receiptId}/download`;
    
    // Set a timeout to hide loader after 10 seconds if PDF doesn't load
    if (receiptLoadTimeout) {
        clearTimeout(receiptLoadTimeout);
    }
    receiptLoadTimeout = setTimeout(function() {
        loader.style.display = 'none';
    }, 7000);
    
    // Create form and submit to iframe
    setTimeout(function() {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = `<?php echo e(url('/receipts')); ?>/${receiptId}/preview`;
        form.target = 'receiptIframe';
        form.style.display = 'none';
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }, 100);
}

function closeReceiptModal() {
    const modal = document.getElementById('receiptPreviewModal');
    const iframe = document.getElementById('receiptIframe');
    const loader = document.getElementById('receiptLoader');
    
    if (receiptLoadTimeout) {
        clearTimeout(receiptLoadTimeout);
    }
    
    modal.style.display = 'none';
    iframe.src = 'about:blank';
    loader.style.display = 'flex';
    currentReceiptUrl = '';
    document.body.style.overflow = ''; // Restore scrolling
}

function receiptLoaded() {
    const loader = document.getElementById('receiptLoader');
    
    if (receiptLoadTimeout) {
        clearTimeout(receiptLoadTimeout);
    }
    
    // Hide loader after a short delay to ensure PDF is rendered
    setTimeout(function() {
        if (loader) {
            loader.style.display = 'none';
        }
    }, 500);
}

function downloadCurrentReceipt() {
    if (currentReceiptUrl) {
        window.location.href = currentReceiptUrl;
    }
}

// Initialize modal event listeners
if (typeof window.receiptModalInitialized === 'undefined') {
    window.receiptModalInitialized = true;
    
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('receiptPreviewModal');
        if (modal) {
            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeReceiptModal();
                }
            });
        }
        
        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                const modal = document.getElementById('receiptPreviewModal');
                if (modal && modal.style.display === 'block') {
                    closeReceiptModal();
                }
            }
        });
    });
}
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/components/receipt-preview-modal.blade.php ENDPATH**/ ?>