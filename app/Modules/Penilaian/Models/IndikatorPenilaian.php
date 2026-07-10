<?php

namespace App\Modules\Penilaian\Models;

use Illuminate\Database\Eloquent\Model;

class IndikatorPenilaian extends Model
{
    protected $table = 'indikator_penilaian';

    protected $fillable = [
        'tujuan_pembelajaran_id',
        'nomor_urut',
        'nama',
        'deskripsi',
        'tipe', // 'guru' or 'industri'
    ];

    /**
     * Relationship with parent Tujuan Pembelajaran.
     */
    public function tujuanPembelajaran()
    {
        return $this->belongsTo(TujuanPembelajaran::class, 'tujuan_pembelajaran_id');
    }
}
