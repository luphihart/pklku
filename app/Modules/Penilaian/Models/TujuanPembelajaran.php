<?php

namespace App\Modules\Penilaian\Models;

use Illuminate\Database\Eloquent\Model;

class TujuanPembelajaran extends Model
{
    protected $table = 'tujuan_pembelajaran';

    protected $fillable = [
        'nomor',
        'nama',
    ];

    /**
     * Relationship with indicators.
     */
    public function indikators()
    {
        return $this->hasMany(IndikatorPenilaian::class, 'tujuan_pembelajaran_id');
    }
}
