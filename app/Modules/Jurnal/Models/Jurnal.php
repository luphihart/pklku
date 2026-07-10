<?php

namespace App\Modules\Jurnal\Models;

use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    protected $table = 'jurnal';

    protected $fillable = [
        'penempatan_pkl_id',
        'tanggal',
        'deskripsi_aktivitas',
        'foto_kegiatan',
        'status_verifikasi',
        'catatan_verifikasi',
        'verified_by',
    ];

    public function penempatanPkl()
    {
        return $this->belongsTo(\App\Modules\PKL\Models\PenempatanPkl::class, 'penempatan_pkl_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(\App\Modules\MasterData\Models\Guru::class, 'verified_by');
    }
}
