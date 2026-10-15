<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Akun extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'tb_akun';

    protected $fillable = [
        'username',
        'password',
        'role',
        'nama',
        'no_telp',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMarketing(): bool
    {
        return $this->role === 'marketing';
    }

    public function isLeads(): bool
    {
        return $this->role === 'leads';
    }

    public function leadsAsMarketing(): HasMany
    {
        return $this->hasMany(Leads::class, 'id_marketing');
    }

    public function leadAccount(): HasMany
    {
        return $this->hasMany(Leads::class, 'id_akun_leads');
    }

    public function kalenderAsMarketing(): HasMany
    {
        return $this->hasMany(Kalender::class, 'id_marketing');
    }

    public function kalenderAsLeads(): HasMany
    {
        return $this->hasMany(Kalender::class, 'id_leads');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ReportMarketing::class, 'id_marketing');
    }
}
