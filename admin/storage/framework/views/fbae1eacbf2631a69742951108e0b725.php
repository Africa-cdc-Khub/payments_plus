<!-- Send Invoice Receipt Modal -->
<div id="sendInvoiceReceiptModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Send Invoice Receipt</h3>
            <span class="close" onclick="closeSendInvoiceReceiptModal()">&times;</span>
        </div>
        <form id="sendInvoiceReceiptForm" method="POST" action="">
            <?php echo csrf_field(); ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="invoiceReceiptEmail">Recipient Email:</label>
                    <input type="email" 
                           id="invoiceReceiptEmail" 
                           name="email" 
                           class="form-control" 
                           required 
                           placeholder="Enter recipient email address">
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    The receipt will be sent as a PDF attachment to the specified email address.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeSendInvoiceReceiptModal()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Receipt
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openSendInvoiceReceiptModal(invoiceId, email = '') {
    const modal = document.getElementById('sendInvoiceReceiptModal');
    const form = document.getElementById('sendInvoiceReceiptForm');
    const emailInput = document.getElementById('invoiceReceiptEmail');
    
    if (!modal || !form || !emailInput) {
        console.error('Send Invoice Receipt modal elements not found');
        return;
    }
    
    // Set form action
    form.action = `<?php echo e(url('/invoices')); ?>/${invoiceId}/receipt/send`;
    
    // Pre-fill email if provided
    emailInput.value = email || '';
    
    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Focus
    setTimeout(() => emailInput.focus(), 100);
}

function closeSendInvoiceReceiptModal() {
    const modal = document.getElementById('sendInvoiceReceiptModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Delegate click for send buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.send-invoice-receipt-btn')) {
        const btn = e.target.closest('.send-invoice-receipt-btn');
        const invoiceId = btn.getAttribute('data-invoice-id');
        const email = btn.getAttribute('data-email') || '';
        openSendInvoiceReceiptModal(invoiceId, email);
    }
});

// ESC close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSendInvoiceReceiptModal();
});

// Close on backdrop click
document.getElementById('sendInvoiceReceiptModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSendInvoiceReceiptModal();
    }
});
</script>
<?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/components/send-invoice-receipt-modal.blade.php ENDPATH**/ ?>