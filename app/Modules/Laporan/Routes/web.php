<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Laporan\Controllers\LaporanController;

Route::middleware(['web', 'auth', 'role:admin,guru'])->group(function () {
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/presensi-excel', [LaporanController::class, 'downloadPresensiExcel'])->name('laporan.presensi_excel');
});

Route::middleware(['web', 'auth', 'role:admin,guru,murid'])->group(function () {
    Route::get('/laporan/nilai-pdf/{placementId}', [LaporanController::class, 'downloadNilaiPdf'])->name('laporan.nilai_pdf');
});

Route::middleware(['web', 'auth', 'role:murid'])->group(function () {
    Route::get('/laporan/murid/jurnal-pdf', [LaporanController::class, 'downloadStudentJournalPdf'])->name('laporan.murid_jurnal_pdf');
    Route::get('/laporan/murid/presensi-pdf', [LaporanController::class, 'downloadStudentAttendancePdf'])->name('laporan.murid_presensi_pdf');
});
