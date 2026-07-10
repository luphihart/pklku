<?php

namespace App\Modules\Pengumuman\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

    protected $fillable = [
        'judul',
        'isi',
        'target_role',
    ];

    public function penerima()
    {
        return $this->hasMany(PengumumanPenerima::class, 'pengumuman_id');
    }
}
