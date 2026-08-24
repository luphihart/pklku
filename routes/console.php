<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal Cron Job: Catat ALPHA otomatis untuk siswa yang tidak presensi dan tidak izin pada jam 23:55 setiap hari
Schedule::command('presensi:auto-absent')->dailyAt('23:55');
