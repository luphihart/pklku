<?php

namespace App\Modules\Penilaian\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianPkl extends Model
{
    protected $table = 'penilaian_pkl';

    protected $fillable = [
        'penempatan_pkl_id',
        'nilai_guru_json',
        'nilai_industri_json',
        'keterangan_tp_json',
        'rata_nilai_guru',
        'rata_nilai_industri',
        'nilai_akhir',
        'predikat',
        'catatan',
    ];

    protected $casts = [
        'nilai_guru_json' => 'array',
        'nilai_industri_json' => 'array',
        'keterangan_tp_json' => 'array',
    ];

    public function penempatanPkl()
    {
        return $this->belongsTo(\App\Modules\PKL\Models\PenempatanPkl::class, 'penempatan_pkl_id');
    }
}
