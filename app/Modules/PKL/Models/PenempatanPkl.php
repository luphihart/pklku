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
    ];

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
