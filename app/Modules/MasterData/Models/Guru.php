<?php

namespace App\Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guru extends Model
{
    use SoftDeletes;

    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nip',
        'nama',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function penempatanBimbingan()
    {
        return $this->hasMany(\App\Modules\PKL\Models\PenempatanPkl::class, 'guru_id');
    }

    public function kunjunganMonitoring()
    {
        return $this->hasManyThrough(
            \App\Modules\PKL\Models\KunjunganMonitoring::class,
            \App\Modules\PKL\Models\PenempatanPkl::class,
            'guru_id',
            'penempatan_pkl_id'
        );
    }
}
