<?php

namespace App\Modules\Pengumuman\Models;

use Illuminate\Database\Eloquent\Model;

class PengumumanPenerima extends Model
{
    protected $table = 'pengumuman_penerima';

    protected $fillable = [
        'pengumuman_id',
        'user_id',
    ];

    public function pengumuman()
    {
        return $this->belongsTo(Pengumuman::class, 'pengumuman_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
