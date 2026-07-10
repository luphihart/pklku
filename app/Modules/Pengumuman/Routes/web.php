<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Pengumuman\Controllers\PengumumanController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
    
    Route::middleware('role:admin')->group(function () {
        Route::post('/pengumuman', [PengumumanController::class, 'store'])->name('pengumuman.store');
        Route::put('/pengumuman/{id}', [PengumumanController::class, 'update'])->name('pengumuman.update');
        Route::delete('/pengumuman/{id}', [PengumumanController::class, 'destroy'])->name('pengumuman.destroy');
    });
});
