<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DguController;
use App\Http\Controllers\GlobalThresholdController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('dgus', DguController::class);

    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/{alert}', [AlertController::class, 'show'])->name('alerts.show');
    Route::post('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge'])
        ->middleware('role:admin')
        ->name('alerts.acknowledge');

    Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/thresholds', [GlobalThresholdController::class, 'index'])->name('thresholds.index');
        Route::get('/thresholds/{threshold}/edit', [GlobalThresholdController::class, 'edit'])->name('thresholds.edit');
        Route::patch('/thresholds/{threshold}', [GlobalThresholdController::class, 'update'])->name('thresholds.update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
