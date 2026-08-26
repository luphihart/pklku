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
    ];

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
