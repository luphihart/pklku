<?php

namespace App\Modules\Presensi\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $table = 'presensi';

    protected $fillable = [
        'penempatan_pkl_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'lat_masuk',
        'lng_masuk',
        'lat_pulang',
        'lng_pulang',
        'foto_masuk',
        'foto_pulang',
        'status_masuk',
        'status_pulang',
    ];

    public function penempatanPkl()
    {
        return $this->belongsTo(\App\Modules\PKL\Models\PenempatanPkl::class, 'penempatan_pkl_id');
    }
}
