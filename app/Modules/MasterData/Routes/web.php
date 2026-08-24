<?php

use Illuminate\Support\Facades\Route;
use App\Modules\MasterData\Controllers\MuridController;
use App\Modules\MasterData\Controllers\GuruController;
use App\Modules\MasterData\Controllers\DudiController;
use App\Modules\MasterData\Controllers\TahunAjaranController;
use App\Modules\MasterData\Controllers\ImportController;

Route::middleware(['web', 'auth', 'role:admin'])->group(function () {
    // Bulk Delete Routes
    Route::post('/master/murid/bulk-delete', [MuridController::class, 'destroyBulk'])->name('murid.destroy_bulk');
    Route::post('/master/guru/bulk-delete', [GuruController::class, 'destroyBulk'])->name('guru.destroy_bulk');
    Route::post('/master/kelas/bulk-delete', [\App\Modules\MasterData\Controllers\KelasController::class, 'destroyBulk'])->name('kelas.destroy_bulk');

    // Reset Password Routes
    Route::post('/master/murid/{id}/reset-password', [MuridController::class, 'resetPassword'])->name('murid.reset_password');
    Route::post('/master/guru/{id}/reset-password', [GuruController::class, 'resetPassword'])->name('guru.reset_password');
    Route::post('/master/murid/bulk-reset-password', [MuridController::class, 'resetPasswordBulk'])->name('murid.reset_password_bulk');
    Route::post('/master/guru/bulk-reset-password', [GuruController::class, 'resetPasswordBulk'])->name('guru.reset_password_bulk');

    // CRUD Resources
    Route::resource('/master/murid', MuridController::class)->except(['show']);
    Route::resource('/master/guru', GuruController::class)->except(['show']);
    Route::resource('/master/dudi', DudiController::class)->except(['show']);
    Route::resource('/master/tahun-ajaran', TahunAjaranController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('/master/jurusan', \App\Modules\MasterData\Controllers\JurusanController::class)->only(['index', 'store', 'update', 'destroy'])->names([
        'index' => 'jurusan.index',
        'store' => 'jurusan.store',
        'update' => 'jurusan.update',
        'destroy' => 'jurusan.destroy',
    ]);
    Route::resource('/master/kelas', \App\Modules\MasterData\Controllers\KelasController::class)->only(['index', 'store', 'update', 'destroy'])->names([
        'index' => 'kelas.index',
        'store' => 'kelas.store',
        'update' => 'kelas.update',
        'destroy' => 'kelas.destroy',
    ]);
    Route::post('/master/hari-libur/sync-national', [\App\Modules\MasterData\Controllers\HariLiburController::class, 'syncNational'])->name('hari-libur.sync_national');
    Route::resource('/master/hari-libur', \App\Modules\MasterData\Controllers\HariLiburController::class)->only(['index', 'store', 'update', 'destroy'])->names([
        'index' => 'hari-libur.index',
        'store' => 'hari-libur.store',
        'update' => 'hari-libur.update',
        'destroy' => 'hari-libur.destroy',
    ]);

    // Import/Export Excel Routes
    Route::get('/import/template/{type}', [ImportController::class, 'downloadTemplate'])->name('import.template');
    Route::post('/import/{type}', [ImportController::class, 'import'])->name('import.store');
});
