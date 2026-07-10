<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Jurnal\Controllers\JurnalController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/jurnal', [JurnalController::class, 'index'])->name('jurnal.index');
    Route::post('/jurnal', [JurnalController::class, 'store'])->name('jurnal.store');
    Route::get('/jurnal/{id}/edit', [JurnalController::class, 'edit'])->name('jurnal.edit');
    Route::put('/jurnal/{id}', [JurnalController::class, 'update'])->name('jurnal.update');
    Route::post('/jurnal/{id}/verify', [JurnalController::class, 'verify'])->name('jurnal.verify');
    Route::delete('/jurnal/{id}', [JurnalController::class, 'destroy'])->name('jurnal.destroy');
});
