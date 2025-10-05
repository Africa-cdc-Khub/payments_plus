<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\SettingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Protected API routes (requires authentication)
Route::middleware(['auth', App\Http\Middleware\EnsureAdminAuthenticated::class])->group(function () {

    // Registrations API
    Route::prefix('registrations')->name('api.registrations.')->group(function () {
        Route::get('/', [RegistrationController::class, 'index'])->middleware('permission:view_registrations');
        Route::get('/stats', [RegistrationController::class, 'stats'])->middleware('permission:view_registrations');
        Route::get('/export', [RegistrationController::class, 'export'])->middleware('permission:export_registrations');
    });

    // Payments API
    Route::prefix('payments')->name('api.payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->middleware('permission:view_payments');
        Route::get('/stats', [PaymentController::class, 'stats'])->middleware('permission:view_payments');
        Route::put('/{id}/status', [PaymentController::class, 'updateStatus'])->middleware('permission:update_payment_status');
        Route::get('/export', [PaymentController::class, 'export'])->middleware('permission:export_payments');
    });

    // Users API
    Route::prefix('users')->name('api.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('permission:view_users');
        Route::get('/stats', [UserController::class, 'stats'])->middleware('permission:view_users');
        Route::put('/{id}/attendance', [UserController::class, 'updateAttendance'])->middleware('permission:manage_ticketing_data');
        Route::get('/export', [UserController::class, 'export'])->middleware('permission:export_users');
    });

    // Admin Users API (Super Admin only)
    Route::prefix('admins')->name('api.admins.')->middleware('role:super_admin')->group(function () {
        Route::get('/', [AdminController::class, 'index']);
        Route::get('/{id}', [AdminController::class, 'show']);
        Route::post('/', [AdminController::class, 'store']);
        Route::put('/{id}', [AdminController::class, 'update']);
        Route::delete('/{id}', [AdminController::class, 'destroy']);
        Route::post('/{id}/toggle-active', [AdminController::class, 'toggleActive']);
        Route::get('/roles', [AdminController::class, 'roles']);
    });

    // Settings API
    Route::prefix('settings')->name('api.settings.')->middleware('permission:manage_settings')->group(function () {
        Route::get('/roles', [SettingsController::class, 'getRoles']);
        Route::get('/roles/{role}/permissions', [SettingsController::class, 'getRolePermissions']);
        Route::get('/permissions', [SettingsController::class, 'getAllPermissions']);
        Route::get('/stats', [SettingsController::class, 'getStats']);
    });

    // Roles API
    Route::prefix('roles')->name('api.roles.')->middleware('role:super_admin')->group(function () {
        Route::get('/{id}', [\App\Http\Controllers\Api\RoleController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Api\RoleController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\Api\RoleController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\RoleController::class, 'destroy']);
    });
});

