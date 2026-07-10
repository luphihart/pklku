<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Monitoring\Controllers\MonitoringController;

Route::middleware(['web', 'auth', 'role:admin,guru'])->group(function () {
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
});
