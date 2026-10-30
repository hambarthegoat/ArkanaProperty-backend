<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Kalender;
use App\Models\Kavling;
use App\Models\Leads;
use App\Models\Perumahan;
use App\Models\ReportMarketing;
use App\Models\TipeRumah;
use Illuminate\Database\Seeder;

/**
 * Demo content mirroring the frontend prototype (Probolinggo projects,
 * blocks, units, two marketing agents and a few leads) so both apps show
 * the same data out of the box. Safe to re-run.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $dimas = Akun::firstOrCreate(['username' => 'dimas'], [
            'password' => 'password', 'role' => 'marketing', 'nama' => 'Dimas Marketing', 'no_telp' => '081211112222',
        ]);
        $santi = Akun::firstOrCreate(['username' => 'santi'], [
            'password' => 'password', 'role' => 'marketing', 'nama' => 'Santi Agent', 'no_telp' => '081233334444',
        ]);

        $projects = [
            [
                'nama_perumahan' => 'Arkana Residence Mayangan',
                'lokasi' => 'Kec. Mayangan, Probolinggo',
                'deskripsi' => 'Hunian subsidi ternyaman di Probolinggo dengan kualitas bangunan komersil. Dekat pusat kota, sekolah, dan area perbelanjaan.',
                'gambar' => 'https://images.unsplash.com/photo-1721815693498-cc28507c0ba2?q=80&w=2070',
                'status' => 'aktif',
                'tipe' => [
                    ['nama_tipe' => 'Tipe 36/60', 'jenis_rumah' => 'subsidi', 'harga' => 162000000, 'dp' => 5000000, 'cicilan' => 900000, 'luas_tanah' => 60, 'luas_bangunan' => 36, 'kamar_tidur' => 2, 'kamar_mandi' => 1, 'spesifikasi' => '2 KT, 1 KM, Dapur, Carport',
                        'kavling' => [['Blok A', 'A-01', 'terjual'], ['Blok A', 'A-02', 'tersedia'], ['Blok A', 'A-03', 'booking'], ['Blok A', 'A-04', 'tersedia'], ['Blok B', 'B-01', 'tersedia'], ['Blok B', 'B-02', 'booking'], ['Blok B', 'B-03', 'tersedia', '1'], ['Blok B', 'B-03', 'booking', '2']]],
                    ['nama_tipe' => 'Tipe 45/84', 'jenis_rumah' => 'premium', 'harga' => 245000000, 'dp' => 25000000, 'cicilan' => 1800000, 'luas_tanah' => 84, 'luas_bangunan' => 45, 'kamar_tidur' => 2, 'kamar_mandi' => 1, 'spesifikasi' => '2 KT, 1 KM, Ruang Keluarga, Carport',
                        'kavling' => [['Blok A', 'A-05', 'terjual'], ['Blok A', 'A-06', 'tersedia']]],
                ],
            ],
            [
                'nama_perumahan' => 'Arkana Premium Kanigaran',
                'lokasi' => 'Kec. Kanigaran, Probolinggo',
                'deskripsi' => 'Cluster premium dengan private garden dan smart home di jantung Kanigaran.',
                'gambar' => 'https://images.unsplash.com/photo-1628744876497-eb30460be9f6?q=80&w=2070',
                'status' => 'aktif',
                'tipe' => [
                    ['nama_tipe' => 'Tipe 60/100', 'jenis_rumah' => 'premium', 'harga' => 450000000, 'dp' => 45000000, 'cicilan' => 3000000, 'luas_tanah' => 100, 'luas_bangunan' => 60, 'kamar_tidur' => 3, 'kamar_mandi' => 2, 'spesifikasi' => '3 KT, 2 KM, Private Garden, Smart Home',
                        'kavling' => [['Blok Premium 1', 'P1-01', 'tersedia'], ['Blok Premium 1', 'P1-03', 'tersedia']]],
                    ['nama_tipe' => 'Tipe 72/120', 'jenis_rumah' => 'premium', 'harga' => 550000000, 'dp' => 55000000, 'cicilan' => 3600000, 'luas_tanah' => 120, 'luas_bangunan' => 72, 'kamar_tidur' => 3, 'kamar_mandi' => 2, 'spesifikasi' => '3 KT, 2 KM, Private Garden, Carport 2 Mobil',
                        'kavling' => [['Blok Premium 1', 'P1-02', 'terjual'], ['Blok Premium 2', 'P2-01', 'tersedia']]],
                ],
            ],
            [
                'nama_perumahan' => 'Scandinavian Hill Phase 2',
                'lokasi' => 'Kec. Wonoasih, Probolinggo',
                'deskripsi' => 'Desain skandinavia dengan attic room dan balkon, dikelilingi area hijau.',
                'gambar' => 'https://images.unsplash.com/photo-1660361338485-c926a27d4eb8?q=80&w=2070',
                'status' => 'pre_launch',
                'tipe' => [
                    ['nama_tipe' => 'Tipe Nordic 50', 'jenis_rumah' => 'premium', 'harga' => 385000000, 'dp' => 38000000, 'cicilan' => 2600000, 'luas_tanah' => 90, 'luas_bangunan' => 50, 'kamar_tidur' => 2, 'kamar_mandi' => 2, 'spesifikasi' => '2 KT, 2 KM, Attic Room',
                        'kavling' => [['Blok Scandi A', 'S1-01', 'booking'], ['Blok Scandi A', 'S1-02', 'tersedia']]],
                    ['nama_tipe' => 'Tipe Nordic 70', 'jenis_rumah' => 'premium', 'harga' => 575000000, 'dp' => 57000000, 'cicilan' => 4000000, 'luas_tanah' => 120, 'luas_bangunan' => 70, 'kamar_tidur' => 3, 'kamar_mandi' => 2, 'spesifikasi' => '3 KT, 2 KM, Attic Room, Balcony',
                        'kavling' => [['Blok Scandi B', 'S2-01', 'tersedia']]],
                ],
            ],
        ];

        $kavlingByNomor = [];

        foreach ($projects as $data) {
            $perumahan = Perumahan::updateOrCreate(
                ['nama_perumahan' => $data['nama_perumahan']],
                collect($data)->except('tipe')->all(),
            );

            foreach ($data['tipe'] as $tipeData) {
                $tipe = TipeRumah::updateOrCreate(
                    ['id_perumahan' => $perumahan->id, 'nama_tipe' => $tipeData['nama_tipe']],
                    collect($tipeData)->except('kavling')->all(),
                );

                foreach ($tipeData['kavling'] as $row) {
                    [$blok, $nomor, $status] = $row;
                    $kavlingByNomor[$nomor] = Kavling::updateOrCreate(
                        ['id_tipe' => $tipe->id, 'nomor_kavling' => $nomor, 'nomor_rumah' => $row[3] ?? null],
                        ['blok' => $blok, 'status' => $status],
                    );
                }
            }
        }

        $mayangan = Perumahan::where('nama_perumahan', 'Arkana Residence Mayangan')->first();
        $kanigaran = Perumahan::where('nama_perumahan', 'Arkana Premium Kanigaran')->first();
        $scandi = Perumahan::where('nama_perumahan', 'Scandinavian Hill Phase 2')->first();

        $ahmad = $this->validatedLead($mayangan, 'Ahmad Rizky', '081234567890', $dimas, 'tinggi', 'Tertarik dengan unit hook, sudah survey Blok A.');
        $siti = $this->validatedLead($mayangan, 'Siti Aminah', '089876543210', $santi, 'sedang', 'Menunggu persetujuan suami untuk booking fee.');

        $kavlingByNomor['A-01']->update(['id_leads' => $ahmad->id]);
        $kavlingByNomor['A-03']->update(['id_leads' => $siti->id]);

        Leads::firstOrCreate(['no_telp' => '081122334455'], [
            'id_perumahan' => $mayangan->id, 'nama_calon' => 'Budi Santoso', 'status_keseriusan' => 'pending',
            'pesan' => 'Ingin tanya jadwal survey lokasi akhir pekan.', 'file_ktp' => null,
        ]);
        Leads::firstOrCreate(['no_telp' => '085566778899'], [
            'id_perumahan' => $kanigaran->id, 'id_tipe' => $kanigaran->tipeRumah()->value('id'), 'nama_calon' => 'Dewi Lestari', 'status_keseriusan' => 'pending',
            'pesan' => 'Mohon info skema KPR Tipe 60/100.',
        ]);
        Leads::firstOrCreate(['no_telp' => '081299887766'], [
            'id_perumahan' => $scandi->id, 'nama_calon' => 'Fajri Hidayat', 'status_keseriusan' => 'rejected',
        ]);

        $agenda = [
            [$dimas, $ahmad, 'Survey Lokasi: Bpk Ahmad', 'survey', now()->addDay()->setTime(9, 0), 'Blok A-01', 'Survey unit bersama keluarga.'],
            [$dimas, $ahmad, 'Batas Pembayaran DP', 'closing', now()->addDays(5)->setTime(15, 0), 'Kantor Pemasaran', 'Pelunasan uang muka Rp 5.000.000.'],
            [$santi, $siti, 'Follow Up: Ibu Siti', 'follow_up', now()->addDays(2)->setTime(13, 30), 'Telepon/WA', 'Konfirmasi booking fee.'],
        ];

        foreach ($agenda as [$marketing, $lead, $judul, $jenis, $tanggal, $lokasi, $deskripsi]) {
            Kalender::firstOrCreate(
                ['id_marketing' => $marketing->id, 'id_leads' => $lead->id_akun_leads, 'judul' => $judul],
                ['jenis' => $jenis, 'tanggal_kegiatan' => $tanggal, 'lokasi' => $lokasi, 'deskripsi_agenda' => $deskripsi],
            );
        }

        $reports = [
            [$dimas, $ahmad, now()->subDay(), 'survey_lokasi', 'Klien sangat tertarik dengan Blok A, menanyakan skema cicilan 15 tahun.'],
            [$santi, $siti, now()->subDay(), 'follow_up', 'Menunggu konfirmasi jadwal kunjungan hari Sabtu depan.'],
            [$dimas, $ahmad, now()->subDays(2), 'closing', 'Tanda tangan PPJB selesai. Berkas sudah diserahkan ke admin.'],
        ];

        foreach ($reports as [$marketing, $lead, $tanggal, $aktivitas, $deskripsi]) {
            ReportMarketing::firstOrCreate(
                ['id_marketing' => $marketing->id, 'id_leads' => $lead->id, 'deskripsi_progres' => $deskripsi],
                ['tanggal_report' => $tanggal->toDateString(), 'jenis_aktivitas' => $aktivitas],
            );
        }
    }

    /**
     * Demo leads accounts use the phone number as username and "password"
     * as password so they can log in to the consumer portal right away.
     */
    private function validatedLead(Perumahan $perumahan, string $nama, string $telp, Akun $marketing, string $prioritas, string $catatan): Leads
    {
        $akun = Akun::firstOrCreate(['username' => $telp], [
            'password' => 'password', 'role' => 'leads', 'nama' => $nama, 'no_telp' => $telp,
        ]);

        return Leads::updateOrCreate(['no_telp' => $telp], [
            'id_perumahan' => $perumahan->id,
            'id_tipe' => $perumahan->tipeRumah()->value('id'),
            'id_marketing' => $marketing->id,
            'id_akun_leads' => $akun->id,
            'nama_calon' => $nama,
            'status_keseriusan' => 'validated',
            'prioritas' => $prioritas,
            'catatan' => $catatan,
        ]);
    }
}
