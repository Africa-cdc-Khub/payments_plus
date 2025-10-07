<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Lobibox JS -->
<script src="https://cdn.jsdelivr.net/npm/lobibox@1.2.7/dist/js/lobibox.min.js"></script>
<!-- reCAPTCHA JS -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<!-- Custom JS -->
<script src="js/registration.js"></script>

<!-- Pass form data to JavaScript for restoration -->
<script>
    window.formData = <?php echo json_encode($formData); ?>;
    window.hasErrors = <?php echo !empty($errors) ? 'true' : 'false'; ?>;
    
    // Function to send payment link via email
    function sendPaymentLink(registrationId) {
        const button = event.target;
        const originalText = button.innerHTML;
        
        // Show loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        button.disabled = true;
        
        // Send AJAX request
        fetch('send_payment_link.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                registration_id: registrationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                button.innerHTML = '<i class="fas fa-check me-2"></i>Invoice Sent!';
                button.classList.remove('btn-outline-primary');
                button.classList.add('btn-success');
                
                // Show success alert
                showAlert('Invoice sent successfully! Check your email.', 'success');
            } else {
                // Show error message
                button.innerHTML = originalText;
                button.disabled = false;
                showAlert('Failed to send invoice. Please try again.', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.innerHTML = originalText;
            button.disabled = false;
            showAlert('An error occurred. Please try again.', 'danger');
        });
    }
    
    // Function to show alerts
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert after the success message
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            successAlert.parentNode.insertBefore(alertDiv, successAlert.nextSibling);
        }
    }
</script>

