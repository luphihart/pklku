<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Setting\Controllers\SettingController;

Route::middleware(['web', 'auth', 'role:admin'])->group(function () {
    Route::get('/setting', [SettingController::class, 'index'])->name('setting.index');
    Route::post('/setting', [SettingController::class, 'update'])->name('setting.update');
});
