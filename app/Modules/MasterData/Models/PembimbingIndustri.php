<?php

namespace App\Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PembimbingIndustri extends Model
{
    use SoftDeletes;

    protected $table = 'pembimbing_industri';

    protected $fillable = [
        'dudi_id',
        'nama',
        'phone',
        'email',
    ];

    public function dudi()
    {
        return $this->belongsTo(Dudi::class, 'dudi_id');
    }

    public function penempatanPkl()
    {
        return $this->hasMany(\App\Modules\PKL\Models\PenempatanPkl::class, 'pembimbing_industri_id');
    }
}
