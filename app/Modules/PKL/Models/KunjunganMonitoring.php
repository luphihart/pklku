<?php

namespace App\Modules\PKL\Models;

use Illuminate\Database\Eloquent\Model;

class KunjunganMonitoring extends Model
{
    protected $table = 'kunjungan_monitoring';

    protected $fillable = [
        'penempatan_pkl_id',
        'tanggal',
        'deskripsi_kunjungan',
        'foto_kunjungan',
        'latitude',
        'longitude',
    ];

    public function penempatanPkl()
    {
        return $this->belongsTo(PenempatanPkl::class, 'penempatan_pkl_id');
    }
}
