<!-- Invoice Receipt Preview Modal -->
<div id="invoiceReceiptPreviewModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full"
 style="display: none; z-index: 10000; position:absolute; background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-6xl shadow-2xl rounded-lg bg-white"
     style="max-width: 90%; margin:0 auto; padding:10px; top:5%; height:90%;">
        <div class="h-full flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4 border-b pb-3">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-receipt text-green-500 mr-2"></i>
                    Receipt Preview
                </h3>
                <div class="flex items-center space-x-3">
                    <button type="button" onclick="downloadInvoiceReceipt()" class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                        <i class="fas fa-download mr-1"></i> Download
                    </button>
                    <button type="button" onclick="closeInvoiceReceiptModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 relative" style="height: calc(100% - 80px);">
                <div id="invoiceReceiptLoader" class="absolute inset-0 flex justify-center items-center bg-white bg-opacity-90" style="display: flex;">
                    <div class="text-center">
                        <div class="spinner border-4 border-gray-300 border-t-green-600 rounded-full w-12 h-12 animate-spin mx-auto mb-4"></div>
                        <p class="text-gray-600">Loading receipt PDF...</p>
                    </div>
                </div>
                <iframe id="invoiceReceiptIframe" 
                        src="" 
                        style="width: 100%; height: 100%; border: none; display: none;">
                </iframe>
            </div>
        </div>
    </div>
</div>

<script>
let currentInvoiceReceiptUrl = '';
let invoiceReceiptLoadTimeout = null;

function openInvoiceReceiptModal(invoiceId) {
    const modal = document.getElementById('invoiceReceiptPreviewModal');
    const iframe = document.getElementById('invoiceReceiptIframe');
    const loader = document.getElementById('invoiceReceiptLoader');
    
    if (!modal || !iframe || !loader) {
        console.error('Invoice receipt modal elements not found');
        return;
    }
    
    // Show modal and loader
    modal.style.display = 'block';
    loader.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Store current invoice ID for download
    currentInvoiceReceiptUrl = `/invoices/${invoiceId}/receipt/download`;
    
    // Set a timeout to hide loader after 10 seconds if PDF doesn't load
    if (invoiceReceiptLoadTimeout) {
        clearTimeout(invoiceReceiptLoadTimeout);
    }
    invoiceReceiptLoadTimeout = setTimeout(function() {
        loader.style.display = 'none';
    }, 7000);
    
    // Create form and submit to iframe
    setTimeout(function() {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = `<?php echo e(url('/invoices')); ?>/${invoiceId}/receipt/preview`;
        form.target = 'invoiceReceiptIframe';
        form.style.display = 'none';
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }, 100);
}

function closeInvoiceReceiptModal() {
    const modal = document.getElementById('invoiceReceiptPreviewModal');
    const iframe = document.getElementById('invoiceReceiptIframe');
    const loader = document.getElementById('invoiceReceiptLoader');
    
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        // Clear iframe and loader
        if (iframe) {
            iframe.src = '';
            iframe.style.display = 'none';
        }
        if (loader) {
            loader.style.display = 'flex';
        }
        
        // Clear timeout
        if (invoiceReceiptLoadTimeout) {
            clearTimeout(invoiceReceiptLoadTimeout);
            invoiceReceiptLoadTimeout = null;
        }
    }
}

function downloadInvoiceReceipt() {
    if (currentInvoiceReceiptUrl) {
        window.open(currentInvoiceReceiptUrl, '_blank');
    }
}

// Handle iframe load
document.addEventListener('DOMContentLoaded', function() {
    const iframe = document.getElementById('invoiceReceiptIframe');
    if (iframe) {
        iframe.addEventListener('load', function() {
            const loader = document.getElementById('invoiceReceiptLoader');
            if (loader) {
                loader.style.display = 'none';
            }
            iframe.style.display = 'block';
            
            // Clear timeout
            if (invoiceReceiptLoadTimeout) {
                clearTimeout(invoiceReceiptLoadTimeout);
                invoiceReceiptLoadTimeout = null;
            }
        });
    }
});

// ESC close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeInvoiceReceiptModal();
});

// Close on backdrop click
document.getElementById('invoiceReceiptPreviewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeInvoiceReceiptModal();
    }
});
</script>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style><?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/components/invoice-receipt-preview-modal.blade.php ENDPATH**/ ?>