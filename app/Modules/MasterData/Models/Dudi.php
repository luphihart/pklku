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

    /**
     * Cast koordinat sebagai string desimal agar presisi tidak hilang saat PHP
     * mengkonversi ke float (floating-point binary rounding).
     * Nilai disimpan di DB sebagai DECIMAL(10,7) sehingga selalu akurat.
     */
    protected $casts = [
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
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
