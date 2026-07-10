<?php

namespace App\Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'tahun',
        'semester',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function penempatanPkl()
    {
        return $this->hasMany(\App\Modules\PKL\Models\PenempatanPkl::class, 'tahun_ajaran_id');
    }
}
