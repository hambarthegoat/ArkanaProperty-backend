<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Akun extends Authenticatable implements FilamentUser, HasName
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
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Only admin accounts may sign in to the Filament panel; marketing and
     * leads use the frontend through the API.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    public function getFilamentName(): string
    {
        return $this->nama ?? $this->username;
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
