<!-- Invoice Receipt Preview Modal -->
<div id="invoiceReceiptPreviewModal" class="modal" style="display: none;">
    <div class="modal-content" style="width: 90%; max-width: 1000px; height: 90%; max-height: 800px;">
        <div class="modal-header">
            <h3>Invoice Receipt Preview</h3>
            <span class="close" onclick="closeInvoiceReceiptModal()">&times;</span>
        </div>
        <div class="modal-body" style="height: calc(100% - 120px); padding: 0;">
            <div id="invoiceReceiptLoader" class="loader" style="display: flex; justify-content: center; align-items: center; height: 100%;">
                <div style="text-align: center;">
                    <div class="spinner" style="border: 4px solid #f3f3f3; border-top: 4px solid #1a5632; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
                    <p>Loading receipt PDF...</p>
                </div>
            </div>
            <iframe id="invoiceReceiptIframe" 
                    src="" 
                    style="width: 100%; height: 100%; border: none; display: none;">
            </iframe>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="downloadInvoiceReceipt()" class="btn btn-primary">
                <i class="fas fa-download"></i> Download Receipt PDF
            </button>
            <button type="button" onclick="closeInvoiceReceiptModal()" class="btn btn-secondary">
                Close
            </button>
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
</style>
<?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/components/invoice-receipt-preview-modal.blade.php ENDPATH**/ ?>