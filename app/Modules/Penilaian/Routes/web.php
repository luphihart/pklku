<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Penilaian\Controllers\PenilaianController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/penilaian', [PenilaianController::class, 'index'])->name('penilaian.index');
    Route::post('/penilaian', [PenilaianController::class, 'store'])->name('penilaian.store')->middleware('role:admin,guru');
    Route::get('/penilaian/template', [PenilaianController::class, 'downloadTemplate'])->name('penilaian.template')->middleware('role:admin,guru');
    Route::post('/penilaian/import', [PenilaianController::class, 'import'])->name('penilaian.import')->middleware('role:admin,guru');
    Route::delete('/penilaian/{id}', [PenilaianController::class, 'destroy'])->name('penilaian.destroy')->middleware('role:admin');
    Route::resource('/master/indikator', \App\Modules\Penilaian\Controllers\IndikatorController::class)->middleware('role:admin,guru')->names([
        'index' => 'indikator.index',
        'store' => 'indikator.store',
        'update' => 'indikator.update',
        'destroy' => 'indikator.destroy',
    ]);

    Route::resource('/master/tujuan-pembelajaran', \App\Modules\Penilaian\Controllers\TujuanPembelajaranController::class)->middleware('role:admin,guru')->names([
        'index' => 'tujuan-pembelajaran.index',
        'store' => 'tujuan-pembelajaran.store',
        'update' => 'tujuan-pembelajaran.update',
        'destroy' => 'tujuan-pembelajaran.destroy',
    ]);
});
