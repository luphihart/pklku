<?php

namespace App\Modules\Presensi\Models;

use Illuminate\Database\Eloquent\Model;

class IzinSakit extends Model
{
    protected $table = 'izin_sakit';

    protected $fillable = [
        'penempatan_pkl_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'tipe',
        'alasan',
        'surat_pendukung',
        'status_approval',
        'approved_by',
        'catatan_guru',
    ];

    public function penempatanPkl()
    {
        return $this->belongsTo(\App\Modules\PKL\Models\PenempatanPkl::class, 'penempatan_pkl_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Modules\MasterData\Models\Guru::class, 'approved_by');
    }
}
