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
    
    // Registrations
    Route::resource('registrations', RegistrationController::class);
    
    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    
    // Admins
    Route::resource('admins', AdminController::class);
    
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
});
