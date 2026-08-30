<?php

namespace App\Modules\PKL\Models;

use Illuminate\Database\Eloquent\Model;

class PenempatanPkl extends Model
{
    protected $table = 'penempatan_pkl';

    protected $fillable = [
        'murid_id',
        'dudi_id',
        'guru_id',
        'pembimbing_industri_id',
        'tahun_ajaran_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'tipe_kerja',
        'hari_wfa',
        'hari_libur',
        'tipe_shift',
        'jam_masuk',
        'batas_terlambat',
        'jam_pulang',
        'tutup_jam_pulang',
    ];

    /**
     * Get the effective shift hours for this placement.
     * Resolves cascading priority:
     * 1. Custom override hours defined on this placement (if tipe_shift == 'custom')
     * 2. Shift Pagi defined by Admin in Settings (if tipe_shift == 'pagi')
     * 3. Shift Siang defined by Admin in Settings (if tipe_shift == 'siang')
     * 4. Regular Global School Settings (fallback)
     */
    public function getEffectiveShiftHours(): array
    {
        $tipe = $this->tipe_shift ?? 'reguler';
        
        static $settings = null;
        if ($settings === null) {
            try {
                $settings = \App\Modules\Setting\Models\Setting::pluck('value', 'key')->all();
            } catch (\Throwable $e) {
                $settings = [];
            }
        }

        $regMasuk       = $settings['jam_masuk'] ?? '07:00';
        $regTerlambat   = $settings['batas_terlambat'] ?? '07:30';
        $regPulang      = $settings['jam_pulang'] ?? '15:00';
        $regTutupPulang = $settings['tutup_jam_pulang'] ?? '21:00';

        if ($tipe === 'custom') {
            $cMasuk       = $this->jam_masuk ? substr($this->jam_masuk, 0, 5) : substr($regMasuk, 0, 5);
            $cTerlambat   = $this->batas_terlambat ? substr($this->batas_terlambat, 0, 5) : substr($regTerlambat, 0, 5);
            $cPulang      = $this->jam_pulang ? substr($this->jam_pulang, 0, 5) : substr($regPulang, 0, 5);
            $cTutupPulang = $this->tutup_jam_pulang ? substr($this->tutup_jam_pulang, 0, 5) : substr($regTutupPulang, 0, 5);

            return [
                'tipe'             => 'custom',
                'label'            => 'Kustom (' . $cMasuk . ' - ' . $cPulang . ')',
                'jam_masuk'        => $cMasuk,
                'batas_terlambat'   => $cTerlambat,
                'jam_pulang'       => $cPulang,
                'tutup_jam_pulang' => $cTutupPulang,
            ];
        }

        if ($tipe === 'pagi') {
            $pagiMasuk       = $this->jam_masuk ?: ($settings['shift_pagi_masuk'] ?? '07:00');
            $pagiTerlambat   = $this->batas_terlambat ?: ($settings['shift_pagi_terlambat'] ?? '07:30');
            $pagiPulang      = $this->jam_pulang ?: ($settings['shift_pagi_pulang'] ?? '15:00');
            $pagiTutupPulang = $this->tutup_jam_pulang ?: ($settings['shift_pagi_tutup_pulang'] ?? '21:00');

            return [
                'tipe'             => 'pagi',
                'label'            => 'Shift Pagi (' . substr($pagiMasuk, 0, 5) . ' - ' . substr($pagiPulang, 0, 5) . ')',
                'jam_masuk'        => substr($pagiMasuk, 0, 5),
                'batas_terlambat'   => substr($pagiTerlambat, 0, 5),
                'jam_pulang'       => substr($pagiPulang, 0, 5),
                'tutup_jam_pulang' => substr($pagiTutupPulang, 0, 5),
            ];
        }

        if ($tipe === 'siang') {
            $siangMasuk       = $this->jam_masuk ?: ($settings['shift_siang_masuk'] ?? '13:00');
            $siangTerlambat   = $this->batas_terlambat ?: ($settings['shift_siang_terlambat'] ?? '13:30');
            $siangPulang      = $this->jam_pulang ?: ($settings['shift_siang_pulang'] ?? '21:00');
            $siangTutupPulang = $this->tutup_jam_pulang ?: ($settings['shift_siang_tutup_pulang'] ?? '23:59');

            return [
                'tipe'             => 'siang',
                'label'            => 'Shift Siang (' . substr($siangMasuk, 0, 5) . ' - ' . substr($siangPulang, 0, 5) . ')',
                'jam_masuk'        => substr($siangMasuk, 0, 5),
                'batas_terlambat'   => substr($siangTerlambat, 0, 5),
                'jam_pulang'       => substr($siangPulang, 0, 5),
                'tutup_jam_pulang' => substr($siangTutupPulang, 0, 5),
            ];
        }

        if ($tipe === 'rolling') {
            if ($detectedShift === 'pagi') {
                $pagiMasuk       = $settings['shift_pagi_masuk'] ?? '06:30';
                $pagiTerlambat   = $settings['shift_pagi_terlambat'] ?? '07:15';
                $pagiPulang      = $settings['shift_pagi_pulang'] ?? '14:30';
                $pagiTutupPulang = $settings['shift_pagi_tutup_pulang'] ?? '21:00';
                return [
                    'tipe'             => 'rolling',
                    'active_shift'     => 'pagi',
                    'label'            => 'Rolling: Shift Pagi (' . substr($pagiMasuk, 0, 5) . ' - ' . substr($pagiPulang, 0, 5) . ')',
                    'jam_masuk'        => substr($pagiMasuk, 0, 5),
                    'batas_terlambat'   => substr($pagiTerlambat, 0, 5),
                    'jam_pulang'       => substr($pagiPulang, 0, 5),
                    'tutup_jam_pulang' => substr($pagiTutupPulang, 0, 5),
                ];
            }
            if ($detectedShift === 'siang') {
                $siangMasuk       = $settings['shift_siang_masuk'] ?? '13:00';
                $siangTerlambat   = $settings['shift_siang_terlambat'] ?? '13:30';
                $siangPulang      = $settings['shift_siang_pulang'] ?? '21:00';
                $siangTutupPulang = $settings['shift_siang_tutup_pulang'] ?? '23:59';
                return [
                    'tipe'             => 'rolling',
                    'active_shift'     => 'siang',
                    'label'            => 'Rolling: Shift Siang (' . substr($siangMasuk, 0, 5) . ' - ' . substr($siangPulang, 0, 5) . ')',
                    'jam_masuk'        => substr($siangMasuk, 0, 5),
                    'batas_terlambat'   => substr($siangTerlambat, 0, 5),
                    'jam_pulang'       => substr($siangPulang, 0, 5),
                    'tutup_jam_pulang' => substr($siangTutupPulang, 0, 5),
                ];
            }

            return [
                'tipe'             => 'rolling',
                'active_shift'     => null,
                'label'            => 'Rolling (Auto-Detect Pagi/Siang)',
                'jam_masuk'        => substr($settings['shift_pagi_masuk'] ?? '06:30', 0, 5),
                'batas_terlambat'   => substr($settings['shift_pagi_terlambat'] ?? '07:15', 0, 5),
                'jam_pulang'       => substr($settings['shift_pagi_pulang'] ?? '14:30', 0, 5),
                'tutup_jam_pulang' => substr($settings['shift_siang_tutup_pulang'] ?? '23:59', 0, 5),
            ];
        }

        // Reguler (default)
        return [
            'tipe'             => 'reguler',
            'label'            => 'Shift Reguler (' . substr($regMasuk, 0, 5) . ' - ' . substr($regPulang, 0, 5) . ')',
            'jam_masuk'        => substr($regMasuk, 0, 5),
            'batas_terlambat'   => substr($regTerlambat, 0, 5),
            'jam_pulang'       => substr($regPulang, 0, 5),
            'tutup_jam_pulang' => substr($regTutupPulang, 0, 5),
        ];
    }

    /**
     * Check if a specific date (or today) is a regular weekly day off / holiday for this placement.
     */
    public function isPlacementHoliday(?string $date = null): bool
    {
        $dateObj = $date ? \Carbon\Carbon::parse($date) : now();
        $daysMap = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];
        $dayNameIndo = $daysMap[$dateObj->format('l')] ?? '';

        // 1. Placement specific custom regular holidays (e.g. 'Sabtu,Minggu' or 'Minggu' or 'Senin')
        if (!empty($this->hari_libur)) {
            $offDays = array_map('trim', explode(',', $this->hari_libur));
            return in_array($dayNameIndo, $offDays);
        }

        // 2. Fallback to DUDI specific working days
        if ($this->dudi && !empty($this->dudi->hari_kerja)) {
            $dudiWorkingDays = array_map('trim', explode(',', $this->dudi->hari_kerja));
            return !in_array($dayNameIndo, $dudiWorkingDays);
        }

        // 3. Fallback to Global Setting working days
        $globalHariKerja = \App\Modules\Setting\Models\Setting::where('key', 'hari_kerja')->value('value') ?: 'Senin,Selasa,Rabu,Kamis,Jumat';
        $globalWorkingDays = array_map('trim', explode(',', $globalHariKerja));
        return !in_array($dayNameIndo, $globalWorkingDays);
    }

    /**
     * Check if the placement is configured as WFA on a given date (or today).
     */
    public function isWfaToday(?string $date = null): bool
    {
        $tipe = $this->tipe_kerja ?? 'wfo';
        if ($tipe === 'wfa') {
            return true;
        }
        if ($tipe === 'wfo') {
            return false;
        }

        // Hybrid: check hari_wfa
        if ($tipe === 'hybrid' && !empty($this->hari_wfa)) {
            $dateObj = $date ? \Carbon\Carbon::parse($date) : now();
            $daysMap = [
                'Monday'    => 'Senin',
                'Tuesday'   => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday'  => 'Kamis',
                'Friday'    => 'Jumat',
                'Saturday'  => 'Sabtu',
                'Sunday'    => 'Minggu',
            ];
            $dayNameIndo = $daysMap[$dateObj->format('l')] ?? '';
            $wfaDays = array_map('trim', explode(',', $this->hari_wfa));
            return in_array($dayNameIndo, $wfaDays);
        }

        return false;
    }

    public function murid()
    {
        return $this->belongsTo(\App\Modules\MasterData\Models\Murid::class, 'murid_id');
    }

    public function dudi()
    {
        return $this->belongsTo(\App\Modules\MasterData\Models\Dudi::class, 'dudi_id');
    }

    public function guru()
    {
        return $this->belongsTo(\App\Modules\MasterData\Models\Guru::class, 'guru_id'); // Guru Pembimbing
    }

    public function pembimbingIndustri()
    {
        return $this->belongsTo(\App\Modules\MasterData\Models\PembimbingIndustri::class, 'pembimbing_industri_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(\App\Modules\MasterData\Models\TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function presensi()
    {
        return $this->hasMany(\App\Modules\Presensi\Models\Presensi::class, 'penempatan_pkl_id');
    }

    public function izinSakit()
    {
        return $this->hasMany(\App\Modules\Presensi\Models\IzinSakit::class, 'penempatan_pkl_id');
    }

    public function jurnal()
    {
        return $this->hasMany(\App\Modules\Jurnal\Models\Jurnal::class, 'penempatan_pkl_id');
    }

    public function kunjunganMonitoring()
    {
        return $this->hasMany(KunjunganMonitoring::class, 'penempatan_pkl_id');
    }

    public function penilaianPkl()
    {
        return $this->hasOne(\App\Modules\Penilaian\Models\PenilaianPkl::class, 'penempatan_pkl_id');
    }
}
