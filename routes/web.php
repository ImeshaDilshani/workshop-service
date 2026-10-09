<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Route structure enforces backend access control via 'role' middleware.
| This is not just UI hiding — unauthorized requests are refused with 403.
|
| Permission matrix from spec:
|   Create user accounts & set roles  → Admin only
|   Add & edit workshops              → Manager only
|   Register & cancel attendees       → Manager, Staff
|   View workshops, registrations     → Manager, Staff
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// All authenticated routes
Route::middleware('auth')->group(function () {

    // Dashboard — all roles (shows role-appropriate content)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile management (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Admin only ──────────────────────────────────────────────
    // Admin can ONLY manage user accounts and view audit logs.
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
    });

    // ── Manager only: workshop creation & editing ───────────────
    Route::middleware('role:manager')->group(function () {
        Route::get('/workshops/create', [WorkshopController::class, 'create'])->name('workshops.create');
        Route::post('/workshops', [WorkshopController::class, 'store'])->name('workshops.store');
        Route::get('/workshops/{workshop}/edit', [WorkshopController::class, 'edit'])->name('workshops.edit');
        Route::put('/workshops/{workshop}', [WorkshopController::class, 'update'])->name('workshops.update');
    });

    // ── Manager & Staff: viewing workshops and managing registrations ──
    Route::middleware('role:manager,staff')->group(function () {
        Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
        Route::get('/workshops/{workshop}', [WorkshopController::class, 'show'])->name('workshops.show');

        // Registrations — manager & staff can register & cancel
        Route::get('/workshops/{workshop}/register', [RegistrationController::class, 'create'])->name('registrations.create');
        Route::post('/workshops/{workshop}/register', [RegistrationController::class, 'store'])->name('registrations.store');
        Route::patch('/registrations/{registration}/cancel', [RegistrationController::class, 'cancel'])->name('registrations.cancel');
        Route::get('/workshops/{workshop}/history', [RegistrationController::class, 'history'])->name('registrations.history');
    });
});

require __DIR__.'/auth.php';
