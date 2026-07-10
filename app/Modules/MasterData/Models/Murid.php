<?php

namespace App\Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Murid extends Model
{
    use SoftDeletes;

    protected $table = 'murid';

    protected $fillable = [
        'user_id',
        'nis',
        'nama',
        'kelas_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function penempatanPkl()
    {
        return $this->hasMany(\App\Modules\PKL\Models\PenempatanPkl::class, 'murid_id');
    }

    public function penempatanAktif()
    {
        return $this->hasOne(\App\Modules\PKL\Models\PenempatanPkl::class, 'murid_id')->where('status', 'aktif');
    }
}
