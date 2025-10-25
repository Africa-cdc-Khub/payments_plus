<!-- Invoice PDF Preview Modal -->
<div id="invoicePreviewModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-6/12" style="display: none; z-index: 10000; position: fixed; top: 0; left: 0; right: 0; bottom: 0;  background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-5 mx-auto p-0 border w-full max-w-7xl shadow-2xl rounded-lg bg-white" style="height: 95vh;  max-width: 50%; margin: 0 auto;">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b bg-gray-50 rounded-t-lg py-2 px-2">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-file-invoice text-blue-500 mr-2"></i>
                Invoice Preview
            </h3>
            <div class="flex items-center gap-2">
                <button type="button" onclick="downloadCurrentInvoice()" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                    <i class="fas fa-download mr-1"></i> Download
                </button>
                <button type="button" onclick="closeInvoiceModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none ml-2">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <!-- Modal Body with iframe -->
        <div class="relative bg-white" style="height: calc(95vh - 64px);">
            <div id="invoiceLoader" class="absolute inset-0 flex items-center justify-center bg-gray-100 z-10" style="min-height: 90vh;">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-3"></i>
                    <p class="text-gray-600 text-lg">Generating Invoice PDF...</p>
                    <p class="text-gray-500 text-sm mt-2">This may take a few seconds</p>
                </div>
            </div>
            <iframe 
                id="invoiceIframe" 
                name="invoiceIframe"
                class="w-full h-full rounded-b-lg"
                style="border: none; background: white;  min-height: 90vh;">
            </iframe>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
let currentInvoiceUrl = '';
let invoiceLoadTimeout = null;

function openInvoiceModal(invoiceId) {
    const modal = document.getElementById('invoicePreviewModal');
    const iframe = document.getElementById('invoiceIframe');
    const loader = document.getElementById('invoiceLoader');
    
    if (!modal || !iframe || !loader) {
        console.error('Invoice modal elements not found');
        return;
    }
    
    // Reset iframe
    iframe.src = 'about:blank';
    
    // Show modal and loader
    modal.style.display = 'block';
    loader.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Store current invoice ID for download
    currentInvoiceUrl = `/invoices/${invoiceId}/download`;
    
    // Set a timeout to hide loader after 10 seconds if PDF doesn't load
    if (invoiceLoadTimeout) {
        clearTimeout(invoiceLoadTimeout);
    }
    invoiceLoadTimeout = setTimeout(function() {
        loader.style.display = 'none';
    }, 7000);
    
    // Create form and submit to iframe
    setTimeout(function() {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = `<?php echo e(url('/invoices')); ?>/${invoiceId}/preview`;
        form.target = 'invoiceIframe';
        form.style.display = 'none';
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }, 100);
}

function closeInvoiceModal() {
    const modal = document.getElementById('invoicePreviewModal');
    const iframe = document.getElementById('invoiceIframe');
    const loader = document.getElementById('invoiceLoader');
    
    if (invoiceLoadTimeout) {
        clearTimeout(invoiceLoadTimeout);
    }
    
    modal.style.display = 'none';
    iframe.src = 'about:blank';
    loader.style.display = 'flex';
    currentInvoiceUrl = '';
    document.body.style.overflow = ''; // Restore scrolling
}

function invoiceLoaded() {
    const loader = document.getElementById('invoiceLoader');
    
    if (invoiceLoadTimeout) {
        clearTimeout(invoiceLoadTimeout);
    }
    
    // Hide loader after a short delay to ensure PDF is rendered
    setTimeout(function() {
        if (loader) {
            loader.style.display = 'none';
        }
    }, 500);
}

function downloadCurrentInvoice() {
    if (currentInvoiceUrl) {
        window.location.href = currentInvoiceUrl;
    }
}

// Initialize modal event listeners
if (typeof window.invoiceModalInitialized === 'undefined') {
    window.invoiceModalInitialized = true;
    
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('invoicePreviewModal');
        if (modal) {
            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeInvoiceModal();
                }
            });
        }
        
        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                const modal = document.getElementById('invoicePreviewModal');
                if (modal && modal.style.display === 'block') {
                    closeInvoiceModal();
                }
            }
        });
    });
}
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/components/invoice-preview-modal.blade.php ENDPATH**/ ?>