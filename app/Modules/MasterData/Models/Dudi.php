<?php

namespace App\Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dudi extends Model
{
    use SoftDeletes;

    protected $table = 'dudi';

    protected $fillable = [
        'nama',
        'alamat',
        'latitude',
        'longitude',
        'radius_meter',
        'pic_nama',
        'pic_phone',
        'hari_kerja',
    ];

    public function pembimbingIndustri()
    {
        return $this->hasMany(PembimbingIndustri::class, 'dudi_id');
    }

    public function penempatanPkl()
    {
        return $this->hasMany(\App\Modules\PKL\Models\PenempatanPkl::class, 'dudi_id');
    }
}
