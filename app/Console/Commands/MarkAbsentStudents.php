<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\PKL\Models\PenempatanPkl;
use App\Modules\Presensi\Models\Presensi;
use App\Modules\Presensi\Models\IzinSakit;
use App\Modules\Setting\Models\Setting;
use Carbon\Carbon;

class MarkAbsentStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presensi:auto-absent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mencatat status ALPHA otomatis untuk murid PKL yang tidak presensi dan tidak memiliki izin/sakit yang disetujui pada hari kerja.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now()->toDateString();

        // 0. Check National / Custom Holiday
        if ($holiday = \App\Modules\MasterData\Models\HariLibur::getHoliday($today)) {
            $this->info("Hari ini ({$today}) adalah hari libur: {$holiday->nama}. Proses auto-absent dilewati.");
            return Command::SUCCESS;
        }

        $daysMap = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];
        $currentDayIndo = $daysMap[Carbon::now()->format('l')];

        $globalHariKerja = Setting::where('key', 'hari_kerja')->value('value') ?: 'Senin,Selasa,Rabu,Kamis,Jumat';
        $globalAllowedDays = array_map('trim', explode(',', $globalHariKerja));

        // Get all active placements
        $placements = PenempatanPkl::with(['dudi', 'murid'])
            ->where('status', 'aktif')
            ->get();

        $countAlpha = 0;
        $countSkipped = 0;

        foreach ($placements as $placement) {
            // Determine working days for this DUDI
            $dudiHariKerja = $placement->dudi?->hari_kerja;
            $allowedDays = $dudiHariKerja 
                ? array_map('trim', explode(',', $dudiHariKerja)) 
                : $globalAllowedDays;

            // 1. Skip if today is NOT a working day for this DUDI
            if (!in_array($currentDayIndo, $allowedDays)) {
                $countSkipped++;
                continue;
            }

            // 2. Check if student already checked in or checked out today
            $hasAttendance = Presensi::where('penempatan_pkl_id', $placement->id)
                ->where('tanggal', $today)
                ->exists();

            if ($hasAttendance) {
                continue;
            }

            // 3. Check if student has approved leave/permission for today
            $hasApprovedLeave = IzinSakit::where('penempatan_pkl_id', $placement->id)
                ->where('status_verifikasi', 'disetujui')
                ->where('tanggal_mulai', '<=', $today)
                ->where('tanggal_selesai', '>=', $today)
                ->exists();

            if ($hasApprovedLeave) {
                continue;
            }

            // 4. Record ALPHA status in database
            Presensi::create([
                'penempatan_pkl_id' => $placement->id,
                'tanggal'           => $today,
                'jam_masuk'         => null,
                'jam_pulang'        => null,
                'lat_masuk'         => null,
                'lng_masuk'         => null,
                'foto_masuk'        => null,
                'status_masuk'      => 'alpha',
                'status_pulang'     => null,
            ]);

            $countAlpha++;
            $this->info("Murid {$placement->murid?->nama} (DUDI: {$placement->dudi?->nama}) dicatat ALPHA untuk tanggal {$today}.");
        }

        $this->info("Proses selesai. Total murid dicatat ALPHA: {$countAlpha}. Total dilewati (hari libur): {$countSkipped}.");

        return Command::SUCCESS;
    }
}
