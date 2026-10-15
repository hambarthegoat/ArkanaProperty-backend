<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Exceptions\LeadValidationException;
use App\Models\Akun;
use App\Models\Leads;
use App\Support\Phone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Shared by the admin API and the Filament panel so both validate leads
 * the same way: KTP required, phone number becomes the portal username,
 * and a random password is returned once for the admin to hand over.
 */
class LeadValidationService
{
    /**
     * @return array{lead: Leads, kredensial: array{username: string, password: ?string, akun_baru: bool}}
     */
    public function validate(Leads $lead, Akun $marketing): array
    {
        if ($lead->status_keseriusan === LeadStatus::Validated) {
            throw new LeadValidationException('Lead ini sudah divalidasi.');
        }

        if (! $lead->hasKtp()) {
            throw new LeadValidationException('KTP belum diunggah. Lengkapi KTP sebelum memvalidasi lead.');
        }

        if (! $marketing->isMarketing()) {
            throw new LeadValidationException('Akun yang dipilih bukan akun marketing.');
        }

        $username = Phone::normalize($lead->no_telp);
        $existing = Akun::where('username', $username)->first();

        if ($existing && ! $existing->isLeads()) {
            throw new LeadValidationException('Nomor HP ini sudah dipakai oleh akun '.$existing->role.'.');
        }

        return DB::transaction(function () use ($lead, $marketing, $username, $existing) {
            $password = null;
            $akun = $existing;

            if (! $akun) {
                $password = Str::password(8, symbols: false);
                $akun = Akun::create([
                    'username' => $username,
                    'password' => $password,
                    'role' => 'leads',
                    'nama' => $lead->nama_calon,
                    'no_telp' => $username,
                ]);
            }

            $lead->update([
                'id_akun_leads' => $akun->id,
                'id_marketing' => $marketing->id,
                'status_keseriusan' => LeadStatus::Validated,
                'prioritas' => $lead->prioritas ?? 'sedang',
            ]);

            return [
                'lead' => $lead->load(['perumahan', 'tipeRumah', 'marketing', 'akunLeads']),
                'kredensial' => [
                    'username' => $akun->username,
                    'password' => $password,
                    'akun_baru' => $password !== null,
                ],
            ];
        });
    }

    public function reject(Leads $lead): Leads
    {
        if ($lead->status_keseriusan === LeadStatus::Validated) {
            throw new LeadValidationException('Lead yang sudah divalidasi tidak bisa ditolak.');
        }

        $lead->update(['status_keseriusan' => LeadStatus::Rejected]);

        return $lead;
    }
}
