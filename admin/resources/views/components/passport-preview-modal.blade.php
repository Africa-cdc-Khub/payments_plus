<!-- Passport Preview Modal -->
<div id="passportPreviewModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-6/12" style="display: none; z-index: 10000; position: fixed; top: 0; left: 0; right: 0; bottom: 0;  background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-5 mx-auto p-0 border w-full max-w-7xl shadow-2xl rounded-lg bg-white" style="height: 95vh;  max-width: 50%; margin: 0 auto;">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b bg-gray-50 rounded-t-lg py-2 px-2">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-passport text-blue-500 mr-2"></i>
                Passport Preview
            </h3>
            <div class="flex items-center gap-2">
                <button type="button" onclick="downloadCurrentPassport()" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                    <i class="fas fa-download mr-1"></i> Download
                </button>
                <button type="button" onclick="closePassportModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none ml-2">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body with iframe -->
        <div class="relative bg-white" style="height: calc(95vh - 64px);">
            <div id="passportLoader" class="absolute inset-0 flex items-center justify-center bg-gray-100 z-10" style="min-height: 90vh;">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-3"></i>
                    <p class="text-gray-600 text-lg">Loading passport...</p>
                    <p class="text-gray-500 text-sm mt-2">This may take a few seconds</p>
                </div>
            </div>
            <iframe
                id="passportIframe"
                name="passportIframe"
                class="w-full h-full rounded-b-lg"
                style="border: none; background: white;  min-height: 90vh;">
            </iframe>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentPassportUrl = '';
let currentRegistrationId = null;
let passportLoadTimeout = null;

function openPassportPreview(passportUrl, registrationId) {
    const modal = document.getElementById('passportPreviewModal');
    const iframe = document.getElementById('passportIframe');
    const loader = document.getElementById('passportLoader');

    if (!modal || !iframe || !loader) {
        console.error('Passport modal elements not found');
        return;
    }

    // Reset iframe
    iframe.src = 'about:blank';

    // Show modal and loader
    modal.style.display = 'block';
    loader.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling

    // Store current passport URL and registration ID for download
    currentPassportUrl = passportUrl;
    currentRegistrationId = registrationId;

    // Set a timeout to hide loader after 10 seconds if passport doesn't load
    if (passportLoadTimeout) {
        clearTimeout(passportLoadTimeout);
    }
    passportLoadTimeout = setTimeout(function() {
        loader.style.display = 'none';
    }, 7000);

    // Load passport in iframe
    setTimeout(function() {
        iframe.src = passportUrl;
    }, 100);
}

function closePassportModal() {
    const modal = document.getElementById('passportPreviewModal');
    const iframe = document.getElementById('passportIframe');
    const loader = document.getElementById('passportLoader');

    if (passportLoadTimeout) {
        clearTimeout(passportLoadTimeout);
    }

    modal.style.display = 'none';
    iframe.src = 'about:blank';
    loader.style.display = 'flex';
    currentPassportUrl = '';
    currentRegistrationId = null;
    document.body.style.overflow = ''; // Restore scrolling
}

function passportLoaded() {
    const loader = document.getElementById('passportLoader');

    if (passportLoadTimeout) {
        clearTimeout(passportLoadTimeout);
    }

    // Hide loader after a short delay to ensure passport is rendered
    setTimeout(function() {
        if (loader) {
            loader.style.display = 'none';
        }
    }, 500);
}

function downloadCurrentPassport() {
    if (currentRegistrationId) {
        // Use the download route with custom filename
        window.location.href = '/approved-delegates/' + currentRegistrationId + '/download-passport';
    } else if (currentPassportUrl) {
        // Fallback to direct URL if registration ID not available
        window.open(currentPassportUrl, '_blank');
    }
}

// Initialize modal event listeners
if (typeof window.passportModalInitialized === 'undefined') {
    window.passportModalInitialized = true;

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('passportPreviewModal');
        if (modal) {
            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closePassportModal();
                }
            });
        }

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                const modal = document.getElementById('passportPreviewModal');
                if (modal && modal.style.display === 'block') {
                    closePassportModal();
                }
            }
        });
    });
}
</script>
@endpush
