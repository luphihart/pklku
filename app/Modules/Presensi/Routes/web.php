<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Presensi\Controllers\PresensiController;
use App\Modules\Presensi\Controllers\IzinSakitController;

Route::middleware(['web', 'auth'])->group(function () {
    // Attendance routes
    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::post('/presensi/checkin', [PresensiController::class, 'checkIn'])->name('presensi.checkin');
    Route::post('/presensi/checkout', [PresensiController::class, 'checkOut'])->name('presensi.checkout');
    Route::post('/presensi/manual', [PresensiController::class, 'storeManual'])->name('presensi.store_manual')->middleware('role:admin');
    Route::put('/presensi/{id}/manual', [PresensiController::class, 'updateManual'])->name('presensi.update_manual')->middleware('role:admin');
    Route::delete('/presensi/{id}', [PresensiController::class, 'destroy'])->name('presensi.destroy')->middleware('role:admin');

    // Leave permission routes
    Route::get('/presensi/izin', [IzinSakitController::class, 'index'])->name('izin.index');
    Route::post('/presensi/izin', [IzinSakitController::class, 'store'])->name('izin.store');
    Route::get('/presensi/izin/{id}/edit', [IzinSakitController::class, 'edit'])->name('izin.edit');
    Route::put('/presensi/izin/{id}', [IzinSakitController::class, 'update'])->name('izin.update');
    Route::post('/presensi/izin/{id}/review', [IzinSakitController::class, 'review'])->name('izin.review');
    Route::delete('/presensi/izin/{id}', [IzinSakitController::class, 'destroy'])->name('izin.destroy')->middleware('role:admin');
});
