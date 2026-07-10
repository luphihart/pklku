<?php

use Illuminate\Support\Facades\Route;
use App\Modules\PKL\Controllers\PenempatanController;
use App\Modules\PKL\Controllers\KunjunganController;

Route::middleware(['web', 'auth'])->group(function () {
    // Admin only routes for Plotting Penempatan
    Route::middleware('role:admin')->group(function () {
        Route::get('/penempatan', [PenempatanController::class, 'index'])->name('penempatan.index');
        Route::post('/penempatan/massal', [PenempatanController::class, 'storeMassal'])->name('penempatan.store_massal');
        Route::put('/penempatan/{id}', [PenempatanController::class, 'update'])->name('penempatan.update');
        Route::delete('/penempatan/{id}', [PenempatanController::class, 'destroy'])->name('penempatan.destroy');
        Route::post('/penempatan/bulk-delete', [PenempatanController::class, 'destroyBulk'])->name('penempatan.destroy_bulk');
        Route::get('/penempatan/pembimbing-industri/{dudiId}', [PenempatanController::class, 'getPembimbingIndustri']);
    });

    // Guru and Admin routes for Kunjungan
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/kunjungan', [KunjunganController::class, 'index'])->name('kunjungan.index');
        Route::post('/kunjungan', [KunjunganController::class, 'store'])->name('kunjungan.store');
    });
});
