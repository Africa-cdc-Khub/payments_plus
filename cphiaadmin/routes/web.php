<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes (not authenticated)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    });

    // Authenticated admin routes
    Route::middleware(['auth', App\Http\Middleware\EnsureAdminAuthenticated::class])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/stats', [DashboardController::class, 'stats'])->name('stats');
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

        // Registrations (all roles can view)
        Route::prefix('registrations')->name('registrations.')->group(function () {
            Route::get('/', function () {
                return view('admin.registrations.simple');
            })->name('index')->middleware('permission:view_registrations');

            Route::get('/{id}', [RegistrationController::class, 'show'])
                ->name('show')
                ->middleware('permission:view_registration_details');
        });

        // Payments (Finance team, Admin, Super Admin)
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', function () {
                return view('admin.payments.index');
            })->name('index')->middleware('permission:view_payments');

            Route::get('/{id}', [PaymentController::class, 'show'])
                ->name('show')
                ->middleware('permission:view_payment_details');
        });

        // Users (Frontend users)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', function () {
                return view('admin.users.index');
            })->name('index')->middleware('permission:view_users');

            Route::get('/{id}', [UserController::class, 'show'])
                ->name('show')
                ->middleware('permission:view_user_details');
        });

        // Admin Users Management (Super Admin only)
        Route::prefix('admins')->name('admins.')->middleware('role:super_admin')->group(function () {
            Route::get('/', function () {
                return view('admin.admins.simple');
            })->name('index');

            Route::get('/test', function () {
                return view('admin.admins.test');
            })->name('test');

            Route::get('/create', function () {
                return view('admin.admins.create');
            })->name('create');

            Route::get('/{id}/edit', function ($id) {
                return view('admin.admins.edit', compact('id'));
            })->name('edit');
        });

        // Role Management (Super Admin only)
        Route::prefix('roles')->name('roles.')->middleware('role:super_admin')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
        });

        // Reports
        Route::prefix('reports')->name('reports.')->middleware('permission:generate_reports')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/registrations', [ReportController::class, 'registrations'])->name('registrations');
            Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
            Route::get('/visa', [ReportController::class, 'visa'])->name('visa');
            Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
            Route::get('/summary', [ReportController::class, 'summary'])->name('summary');
        });

        // Settings (Admin and Super Admin)
        Route::prefix('settings')->name('settings.')->middleware('permission:manage_settings')->group(function () {
            Route::get('/', function () {
                return view('admin.settings.index');
            })->name('index');
        });
    });
});
