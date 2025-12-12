<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\DelegateController;
use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Protected Admin Routes
Route::middleware(['admin.auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Change Password (accessible to all)
    Route::get('change-password', [AdminController::class, 'showChangePasswordForm'])->name('change-password');
    Route::post('change-password', [AdminController::class, 'changePassword'])->name('change-password.update');
    
    // Registrations
    Route::resource('registrations', RegistrationController::class);
    Route::get('registrations/export/csv', [RegistrationController::class, 'export'])->name('registrations.export');
    Route::get('registrations/{registration}/invoice', [RegistrationController::class, 'invoice'])->name('registrations.invoice');
    Route::get('registrations/{registration}/receipt/download', [RegistrationController::class, 'downloadReceipt'])->name('registrations.receipt.download');
    Route::get('registrations/{registration}/receipt/preview', [RegistrationController::class, 'previewReceipt'])->name('registrations.receipt.preview');
    Route::post('registrations/{registration}/receipt/send-pdf', [RegistrationController::class, 'sendReceiptPdf'])->name('registrations.receipt.send-pdf');
    Route::get('registrations/{registration}/mark-paid', function($registrationId) {
        return redirect()->route('registrations.index')->with('error', 'Please use the "Mark as Paid" button in the registrations list to mark a payment as paid.');
    });
    Route::post('registrations/{registration}/mark-paid', [RegistrationController::class, 'markAsPaid'])->name('registrations.mark-paid');
    Route::get('registrations/{registration}/send-invitation', function($registrationId) {
        return redirect()->route('registrations.index')->with('error', 'Please use the "Send Invitation" button in the registrations list to send invitation emails.');
    });
    Route::post('registrations/{registration}/send-invitation', [RegistrationController::class, 'sendInvitation'])->name('registrations.send-invitation');
    
    Route::get('registrations/{registration}/send-receipt', function($registrationId) {
        return redirect()->route('registrations.index')->with('error', 'Please use the "Send Receipt" button in the registrations list to send receipt emails.');
    });
    Route::post('registrations/{registration}/send-receipt', [RegistrationController::class, 'sendReceipt'])->name('registrations.send-receipt');
    
    // Bulk receipt sending
    Route::post('registrations/send-bulk-receipts', [RegistrationController::class, 'sendBulkReceipts'])->name('registrations.send-bulk-receipts');
    
    Route::get('registrations/{registration}/void', function($registrationId) {
        return redirect()->route('registrations.index')->with('error', 'Please use the "Void Registration" button in the registrations list to void registrations.');
    });
    Route::post('registrations/{registration}/void', [RegistrationController::class, 'voidRegistration'])->name('registrations.void');
    
    Route::get('registrations/void-bulk', function() {
        return redirect()->route('registrations.index')->with('error', 'Please use the "Bulk Void" button in the registrations list to void multiple registrations.');
    });
    Route::post('registrations/void-bulk', [RegistrationController::class, 'voidRegistration'])->name('registrations.void-bulk');
    
    Route::get('registrations/{registration}/undo-void', function($registrationId) {
        return redirect()->route('registrations.index')->with('error', 'Please use the "Undo Void" button in the registrations list to undo voided registrations.');
    });
    Route::post('registrations/{registration}/undo-void', [RegistrationController::class, 'undoVoid'])->name('registrations.undo-void');
    
    // Registration Participants
    Route::get('registrations/{registration}/participants', [\App\Http\Controllers\RegistrationParticipantsController::class, 'index'])->name('registration-participants.index');
    Route::post('registrations/{registration}/participants/{participant}/send-invitation', [\App\Http\Controllers\RegistrationParticipantsController::class, 'sendInvitation'])->name('registration-participants.send-invitation');
    Route::post('registrations/{registration}/participants/{participant}/request-passport', [\App\Http\Controllers\RegistrationParticipantsController::class, 'requestPassport'])->name('registration-participants.request-passport');
    
    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/export/csv', [PaymentController::class, 'export'])->name('payments.export');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    
    // Participants
    Route::get('participants', [\App\Http\Controllers\ParticipantsController::class, 'index'])->name('participants.index');
    Route::get('participants/export/csv', [\App\Http\Controllers\ParticipantsController::class, 'export'])->name('participants.export');
    
    // Admins
    Route::resource('admins', AdminController::class);
    Route::post('admins/{admin}/reset-password', [AdminController::class, 'resetPassword'])->name('admins.reset-password');
    
    // Packages
    Route::resource('packages', PackageController::class);
    
    // Invitations
    Route::post('invitations/preview', [InvitationController::class, 'preview'])->name('invitations.preview');
    Route::post('invitations/send', [InvitationController::class, 'send'])->name('invitations.send');
    Route::get('invitations/download/{registration}', [InvitationController::class, 'download'])->name('invitations.download');
    
    // Certificates
    Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('certificates/preview', [CertificateController::class, 'preview'])->name('certificates.preview');
    Route::get('certificates/download', [CertificateController::class, 'download'])->name('certificates.download');
    Route::post('certificates/send', [CertificateController::class, 'send'])->name('certificates.send');
    Route::post('certificates/send-bulk', [CertificateController::class, 'sendBulk'])->name('certificates.send-bulk');
    Route::post('certificates/send-all', [CertificateController::class, 'sendAll'])->name('certificates.send-all');
    
    // Delegates
    Route::get('delegates', [DelegateController::class, 'index'])->name('delegates.index');
    Route::get('delegates/export/csv', [DelegateController::class, 'export'])->name('delegates.export');
    Route::get('delegates/{registration}', [DelegateController::class, 'show'])->name('delegates.show');
    Route::post('delegates/{registration}/approve', [DelegateController::class, 'approve'])->name('delegates.approve');
    Route::post('delegates/{registration}/reject', [DelegateController::class, 'reject'])->name('delegates.reject');
    Route::post('delegates/{registration}/reset-to-pending', [DelegateController::class, 'resetToPending'])->name('delegates.reset-to-pending');
    
    // Invoices (Admin only)
    Route::middleware(['auth:admin'])->group(function () {
        Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
        Route::get('invoices/export/csv', [\App\Http\Controllers\InvoiceController::class, 'export'])->name('invoices.export');
        Route::post('invoices/{invoice}/mark-paid', [\App\Http\Controllers\InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
        Route::post('invoices/{invoice}/cancel', [\App\Http\Controllers\InvoiceController::class, 'cancel'])->name('invoices.cancel');
        Route::get('invoices/{invoice}/download', [\App\Http\Controllers\InvoiceController::class, 'download'])->name('invoices.download');
        Route::get('invoices/{invoice}/preview', [\App\Http\Controllers\InvoiceController::class, 'preview'])->name('invoices.preview');
        Route::get('invoices/{invoice}/email-preview', [\App\Http\Controllers\InvoiceController::class, 'emailPreview'])->name('invoices.email-preview');
        Route::post('invoices/{invoice}/send', [\App\Http\Controllers\InvoiceController::class, 'send'])->name('invoices.send');
        Route::get('invoices/{invoice}/receipt/download', [\App\Http\Controllers\InvoiceController::class, 'downloadReceipt'])->name('invoices.receipt.download');
        Route::get('invoices/{invoice}/receipt/preview', [\App\Http\Controllers\InvoiceController::class, 'previewReceipt'])->name('invoices.receipt.preview');
        Route::post('invoices/{invoice}/receipt/send', [\App\Http\Controllers\InvoiceController::class, 'sendReceipt'])->name('invoices.receipt.send');
    });
    
    // Receipts (Admin only)
    Route::middleware(['auth:admin'])->group(function () {
        Route::resource('receipts', \App\Http\Controllers\ReceiptController::class);
        Route::get('receipts/export/csv', [\App\Http\Controllers\ReceiptController::class, 'export'])->name('receipts.export');
        Route::get('receipts/{receipt}/download', [\App\Http\Controllers\ReceiptController::class, 'download'])->name('receipts.download');
        Route::get('receipts/{receipt}/preview', [\App\Http\Controllers\ReceiptController::class, 'preview'])->name('receipts.preview');
        Route::post('receipts/{receipt}/send', [\App\Http\Controllers\ReceiptController::class, 'send'])->name('receipts.send');
    });
    
    // Approved Delegates
    Route::get('approved-delegates', [\App\Http\Controllers\ApprovedDelegateController::class, 'index'])->name('approved-delegates.index');
    Route::get('approved-delegates/export', [\App\Http\Controllers\ApprovedDelegateController::class, 'export'])->name('approved-delegates.export');
    Route::post('approved-delegates/{registration}/mark-processed', [\App\Http\Controllers\ApprovedDelegateController::class, 'markAsProcessed'])->name('approved-delegates.mark-processed');
    Route::post('approved-delegates/{registration}/request-passport', [\App\Http\Controllers\ApprovedDelegateController::class, 'requestPassport'])->name('approved-delegates.request-passport');
});
