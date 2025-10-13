<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\DelegateController;
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
    Route::post('registrations/{registration}/mark-paid', [RegistrationController::class, 'markAsPaid'])->name('registrations.mark-paid');

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

    // Delegates
    Route::get('delegates', [DelegateController::class, 'index'])->name('delegates.index');
    Route::get('delegates/{registration}', [DelegateController::class, 'show'])->name('delegates.show');
    Route::post('delegates/{registration}/approve', [DelegateController::class, 'approve'])->name('delegates.approve');
    Route::post('delegates/{registration}/reject', [DelegateController::class, 'reject'])->name('delegates.reject');

    // Approved Delegates
    Route::get('approved-delegates', [\App\Http\Controllers\ApprovedDelegateController::class, 'index'])->name('approved-delegates.index');
    Route::get('approved-delegates/export', [\App\Http\Controllers\ApprovedDelegateController::class, 'export'])->name('approved-delegates.export');
    Route::post('approved-delegates/{registration}/mark-processed', [\App\Http\Controllers\ApprovedDelegateController::class, 'markAsProcessed'])->name('approved-delegates.mark-processed');
    Route::post('approved-delegates/{registration}/request-passport', [\App\Http\Controllers\ApprovedDelegateController::class, 'requestPassport'])->name('approved-delegates.request-passport');
    Route::get('approved-delegates/{registration}/download-passport', [\App\Http\Controllers\ApprovedDelegateController::class, 'downloadPassport'])->name('approved-delegates.download-passport');
});
