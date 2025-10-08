<!-- PDF Preview Modal -->
<div id="pdfPreviewModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-6/12" style="display: none; z-index: 10000; position: fixed; top: 0; left: 0; right: 0; bottom: 0;">
    <div class="relative top-5 mx-auto p-0 border w-full max-w-7xl shadow-2xl rounded-lg bg-white" style="height: 95vh;  max-width: 50%; margin: 0 auto;">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b bg-gray-50 rounded-t-lg py-2 px-2">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                Invitation Letter Preview
            </h3>
            <div class="flex items-center gap-2">
                <button type="button" onclick="downloadCurrentPdf()" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                    <i class="fas fa-download mr-1"></i> Download
                </button>
                <button type="button" onclick="closePdfModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none ml-2">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <!-- Modal Body with iframe -->
        <div class="relative bg-white" style="height: calc(95vh - 64px);">
            <div id="pdfLoader" class="absolute inset-0 flex items-center justify-center bg-gray-100 z-10" style="min-height: 90vh;">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-3"></i>
                    <p class="text-gray-600 text-lg">Generating PDF...</p>
                    <p class="text-gray-500 text-sm mt-2">This may take a few seconds</p>
                </div>
            </div>
            <iframe 
                id="pdfIframe" 
                name="pdfIframe"
                class="w-full h-full rounded-b-lg"
                style="border: none; background: white;  min-height: 90vh;">
            </iframe>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentPdfUrl = '';
let pdfLoadTimeout = null;

function openPdfModal(registrationId) {
    const modal = document.getElementById('pdfPreviewModal');
    const iframe = document.getElementById('pdfIframe');
    const loader = document.getElementById('pdfLoader');
    
    if (!modal || !iframe || !loader) {
        console.error('Modal elements not found');
        return;
    }
    
    // Reset iframe
    iframe.src = 'about:blank';
    
    // Show modal and loader
    modal.style.display = 'block';
    loader.style.display = 'flex';
    
    // Store current registration ID for download
    currentPdfUrl = `/invitations/download/${registrationId}`;
    
    // Set a timeout to hide loader after 10 seconds if PDF doesn't load
    if (pdfLoadTimeout) {
        clearTimeout(pdfLoadTimeout);
    }
    pdfLoadTimeout = setTimeout(function() {
        loader.style.display = 'none';
    }, 7000);
    
    // Create form and submit to iframe
    setTimeout(function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("invitations.preview") }}';
        form.target = 'pdfIframe';
        form.style.display = 'none';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        form.appendChild(tokenInput);
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'registration_id';
        idInput.value = registrationId;
        form.appendChild(idInput);
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }, 100);
}

function closePdfModal() {
    const modal = document.getElementById('pdfPreviewModal');
    const iframe = document.getElementById('pdfIframe');
    const loader = document.getElementById('pdfLoader');
    
    if (pdfLoadTimeout) {
        clearTimeout(pdfLoadTimeout);
    }
    
    modal.style.display = 'none';
    iframe.src = 'about:blank';
    loader.style.display = 'flex';
    currentPdfUrl = '';
}

function pdfLoaded() {
    const loader = document.getElementById('pdfLoader');
    
    if (pdfLoadTimeout) {
        clearTimeout(pdfLoadTimeout);
    }
    
    // Hide loader after a short delay to ensure PDF is rendered
    setTimeout(function() {
        if (loader) {
            loader.style.display = 'none';
        }
    }, 500);
}

function downloadCurrentPdf() {
    if (currentPdfUrl) {
        window.location.href = currentPdfUrl;
    }
}

// Initialize modal event listeners
if (typeof window.pdfModalInitialized === 'undefined') {
    window.pdfModalInitialized = true;
    
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('pdfPreviewModal');
        if (modal) {
            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closePdfModal();
                }
            });
        }
        
        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                const modal = document.getElementById('pdfPreviewModal');
                if (modal && modal.style.display === 'block') {
                    closePdfModal();
                }
            }
        });
    });
}
</script>
@endpush

