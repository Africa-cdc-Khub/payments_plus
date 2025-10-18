<?php

use App\Http\Controllers\Api\InvitationApiController;
use App\Http\Controllers\Api\RegistrationApiController;
use Illuminate\Support\Facades\Route;

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

// Invitation API - Localhost only
Route::post('/invitation/send', [InvitationApiController::class, 'send'])
    ->name('api.invitation.send');

Route::get('/invitation/send', [InvitationApiController::class, 'send'])
    ->name('api.invitation.send.get');

// Registration API - Localhost only
Route::get('/registrations', [RegistrationApiController::class, 'index'])
    ->name('api.registrations.index');

Route::get('/registrations/stats', [RegistrationApiController::class, 'stats'])
    ->name('api.registrations.stats');

Route::get('/registrations/{id}', [RegistrationApiController::class, 'show'])
    ->name('api.registrations.show');

