<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'tanggal_lahir',
        'password',
        'role',
        'phone',
        'photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tanggal_lahir' => 'date',
        ];
    }

    // Module Relationships

    public function guru()
    {
        return $this->hasOne(\App\Modules\MasterData\Models\Guru::class, 'user_id');
    }

    public function murid()
    {
        return $this->hasOne(\App\Modules\MasterData\Models\Murid::class, 'user_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(\App\Modules\System\Models\AuditLog::class, 'user_id');
    }

    public function pengumumanPenerima()
    {
        return $this->hasMany(\App\Modules\Pengumuman\Models\PengumumanPenerima::class, 'user_id');
    }
}
