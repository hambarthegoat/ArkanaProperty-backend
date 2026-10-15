# ArkanaProperty Backend

Backend REST API dan **admin panel** untuk **ArkanaProperty** — sistem Property Marketing & CRM untuk perusahaan developer (PT). Dibangun dengan **Laravel 13**, **PHP 8.3+**, **Filament 5** (admin panel), autentikasi **Laravel Sanctum**, dan database **PostgreSQL** (Supabase).

---

## 1. Requirements

- PHP **8.3 atau lebih baru** (`composer.json` mengunci platform ke PHP 8.3 agar `composer install` jalan di 8.3 maupun 8.4)
- Ekstensi PHP wajib:
  - `pdo_pgsql` dan `pgsql` (driver PostgreSQL)
  - `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`, `fileinfo`
- [Composer](https://getcomposer.org/) 2.x
- Akses ke database PostgreSQL (project ini menggunakan Supabase, kredensial sudah ada di `.env`)

---

## 2. Clone & Install Dependencies

```bash
git clone <repo-url> ArkanaProperty-backend
cd ArkanaProperty-backend
composer install
```

> Jika muncul error terkait *security advisory* saat `composer install`, jalankan:
> ```bash
> composer config audit.abandoned report
> composer install --no-audit
> ```

---

## 3. Konfigurasi Environment (`.env`)

Salin file contoh lalu sesuaikan:

```bash
# Linux / macOS
cp .env.example .env

# Windows (Command Prompt)
copy .env.example .env

# Windows (PowerShell)
Copy-Item .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Atur koneksi database PostgreSQL (Supabase) di `.env`:

```env
APP_NAME=ArkanaProperty
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=aws-1-ap-southeast-1.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.xxxxxxxxxxxxxxxxxx
DB_PASSWORD=your_supabase_password
```

> Ganti `DB_USERNAME` dan `DB_PASSWORD` sesuai kredensial Supabase project Anda. Jangan commit `.env` ke repository.

---

## 4. Mengaktifkan Ekstensi PHP `pdo_pgsql` / `pgsql`

Aplikasi ini **tidak akan jalan tanpa driver PostgreSQL**. Cek dulu apakah sudah aktif:

```bash
php -m | grep -i pgsql
```

Jika kosong (tidak muncul `pdo_pgsql` dan `pgsql`), aktifkan sesuai OS:

### Linux (Ubuntu/Debian)

```bash
sudo apt update
sudo apt install php-pgsql
```

### Linux (Arch / Manjaro)

```bash
sudo pacman -S php-pgsql
```

Jika ekstensi sudah terpasang tapi belum aktif di `php.ini` (cek lokasi dengan `php --ini`), tambahkan baris berikut ke `php.ini`:

```ini
extension=pdo_pgsql
extension=pgsql
```

Atau jalankan command Artisan dengan flag `-d` tanpa mengubah `php.ini`:

```bash
php -d extension=pdo_pgsql.so -d extension=pgsql.so artisan migrate
```

### Windows (XAMPP / WAMP / php.ini manual)

1. Buka file `php.ini` (untuk XAMPP biasanya di `C:\xampp\php\php.ini`).
2. Pastikan baris berikut **tidak diawali tanda `;`** (uncomment):
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```
3. Simpan file, lalu restart Apache/PHP service (jika menggunakan XAMPP, restart dari Control Panel).
4. Verifikasi:
   ```cmd
   php -m | findstr pgsql
   ```

---

## 5. Migrasi Database & Seeder

Jalankan migrasi untuk membuat semua tabel (`tb_perumahan`, `tb_akun`, `tb_tipe_rumah`, `tb_kavling`, `tb_leads`, `tb_kalender`, `tb_report_marketing`, dll):

```bash
php artisan migrate
```

Jalankan seeder untuk membuat akun admin default dan data demo (perumahan, tipe rumah, kavling, marketing, leads, agenda, report — sama dengan konten frontend):

```bash
php artisan db:seed
```

Akun yang dibuat seeder:

| Username        | Password   | Role      |
|-----------------|------------|-----------|
| `admin`         | `password` | admin     |
| `dimas`         | `password` | marketing |
| `santi`         | `password` | marketing |
| `081234567890`  | `password` | leads (Ahmad Rizky) |
| `089876543210`  | `password` | leads (Siti Aminah) |

> Seeder aman dijalankan ulang. Untuk produksi, jalankan hanya `php artisan db:seed --class=AdminSeeder` lalu ganti password admin.

> **Catatan:** Jika ekstensi `pdo_pgsql` belum aktif secara global, tambahkan flag `-d` di setiap command Artisan, contoh:
> ```bash
> php -d extension=pdo_pgsql.so -d extension=pgsql.so artisan migrate --seed
> ```

---

## 6. Storage & File Upload

- **KTP** (`POST /api/public/leads`, `POST /api/admin/leads/{id}/ktp`) disimpan **privat** di disk `local` (`storage/app/private/ktp`) dan hanya bisa diunduh admin lewat `GET /api/admin/leads/{id}/ktp` atau admin panel.
- **Gambar perumahan / tipe rumah** disimpan di disk `public`. Buat symbolic link agar bisa diakses publik:

```bash
php artisan storage:link
```

Pastikan `APP_URL` di `.env` sesuai alamat backend (mis. `http://127.0.0.1:8000`) karena dipakai untuk membentuk `gambar_url`.

> Di Windows, command ini juga berfungsi (membuat symlink). Jika gagal karena permission, jalankan terminal/Command Prompt **sebagai Administrator**.

---

## 7. Menjalankan Server

### Linux / macOS

```bash
php artisan serve
```

Jika driver PostgreSQL belum aktif di `php.ini`:

```bash
php -d extension=pdo_pgsql.so -d extension=pgsql.so -S 127.0.0.1:8000 -t public public/index.php
```

### Windows

```cmd
php artisan serve
```

Aplikasi akan berjalan di `http://127.0.0.1:8000`.

---

## 8. Admin Panel (Filament)

Admin panel tersedia di `http://127.0.0.1:8000/admin` dan memakai data yang sama dengan API.

- Login memakai **username** dari `tb_akun` (guard `admin`); hanya akun `role = admin` yang bisa masuk. Akun marketing/leads memakai aplikasi frontend.
- **Dashboard**: statistik leads & unit, tabel leads terbaru.
- **Properti → Perumahan**: CRUD perumahan + gambar, tipe rumah dikelola di dalam halaman edit perumahan. Kolom jumlah unit tersedia/booking/terjual.
- **Properti → Kavling**: CRUD kavling, ubah status langsung dari tabel, filter per perumahan/blok/status. Status `Tersedia` otomatis melepas pembeli.
- **Penjualan → Leads**: aksi **Validasi** (pilih marketing → akun portal dibuat dengan username = nomor HP, password tampil sekali di notifikasi), **Tolak**, **Unduh KTP**, upload KTP susulan di halaman detail. Lead tidak bisa dibuat manual (hanya dari form minat).
- **Marketing → Akun Marketing / Report Marketing / Agenda**: kelola akun marketing; report dan agenda read-only (diisi marketing dari frontend).

Aset Filament (`public/css|js|fonts/filament`) di-generate otomatis oleh `composer install` (`php artisan filament:upgrade`), tidak di-commit.

---

## 9. Dokumentasi API

Kontrak lengkap (request, response, enum) ada di [`docs/arkanaproperty-api.yaml`](docs/arkanaproperty-api.yaml) (OpenAPI 3). Semua response memakai format:

```json
{
  "success": true,
  "data": { },
  "message": "Success"
}
```

Konvensi: field snake_case berbahasa Indonesia, uang dikirim sebagai integer rupiah, enum dikirim sebagai value + `*_label`, error validasi 422 dengan `message: "Validation failed"` dan `data` berisi error per field.

### Auth

| Method | Endpoint            | Akses  | Deskripsi |
|--------|---------------------|--------|-----------|
| POST   | `/api/auth/login`   | Public | Login (admin/marketing/leads), return Sanctum token. Leads login dengan username = nomor HP |
| GET    | `/api/auth/me`      | Auth   | Akun yang sedang login |
| POST   | `/api/auth/logout`  | Auth   | Revoke token aktif |

### Tamu / Public

| Method | Endpoint                     | Deskripsi |
|--------|-------------------------------|-----------|
| GET    | `/api/public/filters`         | Opsi filter katalog (lokasi, jenis rumah, rentang harga) |
| GET    | `/api/public/perumahan`       | Katalog. Query: `lokasi`, `jenis_rumah`, `min_budget`, `max_budget`, `max_cicilan` |
| GET    | `/api/public/perumahan/{id}`  | Detail perumahan + tipe rumah + kavling + jumlah unit |
| GET    | `/api/public/tipe-rumah/{id}` | Detail tipe rumah (halaman detail properti) |
| POST   | `/api/public/leads`           | Form ketertarikan (`id_tipe`/`id_perumahan`, `nama_calon`, `no_telp`, `pesan`, `file_ktp` opsional) |

### Admin (`auth:sanctum`, `role:admin`)

| Method | Endpoint                          | Deskripsi |
|--------|-------------------------------------|-----------|
| GET    | `/api/admin/dashboard`              | Statistik leads & unit, leads/report terbaru |
| GET    | `/api/admin/perumahan`              | List perumahan + tipe rumah + jumlah unit per status |
| POST   | `/api/admin/perumahan`              | Tambah perumahan + tipe rumah |
| GET    | `/api/admin/perumahan/{id}`         | Detail perumahan |
| GET    | `/api/admin/kavling`                | List kavling. Query: `id_perumahan`, `id_tipe`, `blok`, `status`, `search` |
| POST   | `/api/admin/tipe-rumah/{idTipe}/kavling` | Tambah kavling (`blok`, `nomor_kavling`, `status`) |
| PUT    | `/api/admin/kavling/{id}`           | Update status (`tersedia`/`booking`/`terjual`), pembeli (`id_leads`), blok/nomor |
| GET    | `/api/admin/leads`                  | List leads. Query: `status`, `search` |
| GET    | `/api/admin/leads/{id}`             | Detail lead |
| POST   | `/api/admin/leads/{id}/validate`    | Validasi (KTP wajib ada) atau tolak lead. Validasi membuat/memakai akun leads dengan username = nomor HP dan mengembalikan password sekali |
| POST   | `/api/admin/leads/{id}/ktp`         | Upload/ganti KTP |
| GET    | `/api/admin/leads/{id}/ktp`         | Unduh KTP |
| GET    | `/api/admin/reports`                | Rekap report marketing. Query: `id_marketing`, `from`, `to` |
| GET    | `/api/admin/marketing`              | List akun marketing |
| POST   | `/api/admin/marketing`              | Buat akun marketing |

### Marketing (`auth:sanctum`, `role:marketing`)

| Method | Endpoint                      | Deskripsi |
|--------|--------------------------------|-----------|
| GET    | `/api/marketing/leads`         | Leads yang ditugaskan |
| GET    | `/api/marketing/leads/{id}`    | Detail lead yang ditugaskan |
| PATCH  | `/api/marketing/leads/{id}`    | Update `prioritas` & `catatan` |
| GET    | `/api/marketing/calendar`      | Agenda milik sendiri. Query: `from`, `to` |
| POST   | `/api/marketing/calendar`      | Buat agenda (`id_lead`, `judul`, `jenis`, `tanggal_kegiatan`, `lokasi`, `deskripsi_agenda`) |
| GET    | `/api/marketing/reports`       | Riwayat report milik sendiri |
| POST   | `/api/marketing/reports`       | Submit report (`tanggal_report`, `id_lead`, `jenis_aktivitas`, `deskripsi_progres`) |

### Leads / Portal Konsumen (`auth:sanctum`, `role:leads`)

| Method | Endpoint                       | Deskripsi |
|--------|----------------------------------|-----------|
| GET    | `/api/leads/profile`             | Akun, lead, unit, marketing & timeline pembelian |
| GET    | `/api/leads/calendar`            | Agenda mendatang (`?semua=1` untuk semua) |
| GET    | `/api/leads/marketing-contact`   | Kontak marketing (nama, no. WA, link wa.me) |

---

## 10. Testing Cepat dengan cURL

Login sebagai admin:

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}'
```

Gunakan token dari response untuk mengakses endpoint yang membutuhkan autentikasi:

```bash
curl http://127.0.0.1:8000/api/admin/perumahan \
  -H "Authorization: Bearer <token>"
```

---

## 11. Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `could not find driver (Connection: pgsql)` | Ekstensi `pdo_pgsql` belum aktif. Lihat bagian [4](#4-mengaktifkan-ekstensi-php-pdo_pgsql--pgsql). |
| `SQLSTATE[08006] could not connect to server` | Cek kembali `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` di `.env`, serta koneksi internet. |
| Response HTML 404/500 padahal endpoint API | Pastikan `routes/api.php` ter-load (cek `php artisan route:list`) dan request mengarah ke `/api/...`. |
| `Route [login] not defined` saat akses tanpa token | Sudah ditangani via custom exception handler di `bootstrap/app.php`, akan otomatis return JSON `401 Unauthenticated`. |
| Gambar perumahan tidak tampil | Jalankan `php artisan storage:link` dan pastikan `APP_URL` benar. KTP memang tidak bisa diakses via URL publik; unduh lewat `GET /api/admin/leads/{id}/ktp`. |
