<?php

use Illuminate\Support\Facades\Route;
use App\Modules\System\Controllers\SystemController;

Route::middleware(['web', 'auth', 'role:admin'])->group(function () {
    Route::get('/system', [SystemController::class, 'index'])->name('system.index');
    Route::get('/system/backup', [SystemController::class, 'downloadBackup'])->name('system.backup');
    Route::post('/system/restore', [SystemController::class, 'restoreBackup'])->name('system.restore');
    Route::post('/system/clear-logs', [SystemController::class, 'clearLogs'])->name('system.clear_logs');
});
