<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AppNotificationController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DguController;
use App\Http\Controllers\DguOperationalController;
use App\Http\Controllers\GlobalThresholdController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/map', [MapController::class, 'index'])->name('map.index');
    Route::get('/management', [ManagementController::class, 'index'])->name('management.index');

    Route::post('dgus/{dgu}/operational', [DguOperationalController::class, 'update'])
        ->name('dgus.operational.update');

    Route::resource('dgus', DguController::class);

    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/{alert}', [AlertController::class, 'show'])->name('alerts.show');
    Route::post('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge'])
        ->middleware('role:admin')
        ->name('alerts.acknowledge');

    Route::get('/notifications', [AppNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [AppNotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [AppNotificationController::class, 'read'])->name('notifications.read');

    Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/thresholds', [GlobalThresholdController::class, 'index'])->name('thresholds.index');
        Route::get('/thresholds/{threshold}/edit', [GlobalThresholdController::class, 'edit'])->name('thresholds.edit');
        Route::patch('/thresholds/{threshold}', [GlobalThresholdController::class, 'update'])->name('thresholds.update');

        Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('/logs', [SystemLogController::class, 'index'])->name('logs.index');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
