<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public Guest Routes
Route::get('/invitation/{qr_code}', [\App\Http\Controllers\PublicController::class, 'invitation'])->name('public.invitation');
Route::post('/rsvp/{qr_code}', [\App\Http\Controllers\PublicController::class, 'rsvp'])->name('public.rsvp');


// Backward compatibility or direct checkin
Route::get('/checkin/{qr_code}', [ScanController::class, 'checkin'])->name('checkin.public');
Route::post('/self-checkin', [ScanController::class, 'selfCheckin'])->name('checkin.self');
Route::get('/qr/{qr_code}', [ScanController::class, 'publicQr'])->name('public.qr');

Route::get('/', function () {
    return Inertia\Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// Authenticated routes
Route::middleware(['auth', 'verified', 'require.otp'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User & Event Management (superadmin only for create, store, destroy)
    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('events/search', [EventController::class, 'search'])->name('events.search');
        Route::post('events/bulk-delete', [EventController::class, 'bulkDelete'])->name('events.bulk-delete');
        Route::resource('events', EventController::class)->only(['create', 'store', 'destroy']);
        
        Route::post('users/{user}/events', [UserController::class, 'syncEvents'])->name('users.events.sync');
        Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
        Route::resource('users', UserController::class);
    });

    // Event Management (accessible to admins who own the event)
    Route::middleware(['role:superadmin|admin_event|admin', 'event.admin'])->group(function () {
        Route::resource('events', EventController::class)->only(['index', 'show', 'edit', 'update']);
    });

    // Guests - Accessible to both superadmin and admin/admin_event
    Route::middleware(['role:superadmin|admin_event|admin', 'event.admin'])->group(function () {
        Route::post('guests/import', [GuestController::class, 'import'])->name('guests.import');
        Route::post('guests/bulk-whatsapp', [GuestController::class, 'bulkSendMessage'])->name('guests.bulk-whatsapp');
        Route::post('guests/bulk-delete', [GuestController::class, 'bulkDelete'])->name('guests.bulk-delete');
        Route::resource('guests', GuestController::class);
        Route::post('guests/{guest}/send-whatsapp', [GuestController::class, 'sendWhatsApp'])->name('guests.send-whatsapp');
        Route::get('guests/{guest}/qr', [GuestController::class, 'generateQr'])->name('guests.qr');
        Route::get('guests/{guest}/pdf', [GuestController::class, 'downloadPdf'])->name('guests.pdf');
    });

    // Checkin Log - Read-only + Export
    Route::middleware(['role:superadmin|admin_event|admin', 'event.admin'])->group(function () {
        Route::get('checkins', [\App\Http\Controllers\CheckinController::class, 'index'])->name('checkins.index');
        Route::get('checkins/poll', [\App\Http\Controllers\CheckinController::class, 'poll'])->name('checkins.poll');
        Route::get('checkins/export/csv', [\App\Http\Controllers\CheckinController::class, 'exportCsv'])->name('checkins.export.csv');
        Route::get('checkins/export/pdf', [\App\Http\Controllers\CheckinController::class, 'exportPdf'])->name('checkins.export.pdf');
    });

    // Scanner - Accessible to both superadmin and admin/admin_event
    Route::prefix('scan')->middleware(['role:superadmin|admin_event|admin', 'event.admin'])->name('scanner.')->group(function () {
        Route::get('/', [ScanController::class, 'index'])->name('index');
        Route::post('/process', [ScanController::class, 'scan'])->name('scan');
        Route::post('/search', [ScanController::class, 'search'])->name('search');
        Route::post('/manual', [ScanController::class, 'processManual'])->name('manual');
        Route::post('/offline-download', [ScanController::class, 'downloadOfflineData'])->name('offline.download');
        Route::post('/offline-sync', [ScanController::class, 'syncOfflineData'])->name('offline.sync');
    });

    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
