# ArkanaProperty Backend

Backend REST API untuk **ArkanaProperty** — sistem Property Marketing & CRM untuk perusahaan developer (PT). Dibangun dengan **Laravel 13**, **PHP 8.3+**, autentikasi **Laravel Sanctum**, dan database **PostgreSQL** (Supabase).

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

Jalankan seeder untuk membuat akun admin default:

```bash
php artisan db:seed
```

Akun admin default yang akan dibuat:

| Username | Password   | Role  |
|----------|-----------|-------|
| `admin`  | `password` | admin |

> **Catatan:** Jika ekstensi `pdo_pgsql` belum aktif secara global, tambahkan flag `-d` di setiap command Artisan, contoh:
> ```bash
> php -d extension=pdo_pgsql.so -d extension=pgsql.so artisan migrate --seed
> ```

---

## 6. Storage Link (untuk Upload KTP)

Endpoint `POST /api/public/leads` mengunggah file KTP ke `storage/app/public/ktp`. Buat symbolic link agar file bisa diakses publik:

```bash
php artisan storage:link
```

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

## 8. Dokumentasi API

Semua response API menggunakan format standar:

```json
{
  "success": true,
  "data": { },
  "message": "Success"
}
```

### Auth

| Method | Endpoint            | Akses  | Deskripsi                          |
|--------|---------------------|--------|-------------------------------------|
| POST   | `/api/auth/login`   | Public | Login (admin/marketing/leads), return Sanctum token |
| POST   | `/api/auth/logout`  | Auth   | Revoke token aktif                  |

### Tamu / Public

| Method | Endpoint                     | Deskripsi |
|--------|-------------------------------|-----------|
| GET    | `/api/public/perumahan`       | Katalog perumahan. Query filter: `lokasi`, `min_budget`, `max_budget`, `lama_angsuran`, `jenis_rumah` |
| GET    | `/api/public/perumahan/{id}`  | Detail perumahan, termasuk tipe rumah & kavling tersedia |
| POST   | `/api/public/leads`           | Submit form ketertarikan (`id_perumahan`, `nama_calon`, `no_telp`, `file_ktp`) |

### Admin (`auth:sanctum`, `role:admin`)

| Method | Endpoint                          | Deskripsi |
|--------|-------------------------------------|-----------|
| GET    | `/api/admin/perumahan`              | List semua perumahan |
| POST   | `/api/admin/perumahan`              | Tambah perumahan + tipe rumah |
| POST   | `/api/admin/tipe-rumah/{idTipe}/kavling` | Tambah kavling ke tipe rumah (`nomor_kavling`, `status` opsional) |
| PUT    | `/api/admin/kavling/{id}`           | Update status kavling (`tersedia` / `terjual`) |
| GET    | `/api/admin/leads`                  | List semua leads |
| POST   | `/api/admin/leads/{id}/validate`    | Validasi KTP. Jika `is_valid=true`, sistem otomatis membuat akun `tb_akun` (role `leads`), assign `id_marketing`, dan update `id_akun_leads` |
| GET    | `/api/admin/reports`                | Rekap report harian marketing |
| GET    | `/api/admin/marketing`              | List semua akun marketing |
| POST   | `/api/admin/marketing`              | Buat akun marketing baru (`username`, `password`, `nama`, `no_telp`) |

### Marketing (`auth:sanctum`, `role:marketing`)

| Method | Endpoint                      | Deskripsi |
|--------|--------------------------------|-----------|
| GET    | `/api/marketing/leads`         | List leads yang ditugaskan ke marketing login |
| GET    | `/api/marketing/leads/{id}`    | Detail leads yang ditugaskan |
| POST   | `/api/marketing/calendar`      | Buat agenda/reminder untuk leads (`id_leads`, `tanggal_kegiatan`, `deskripsi_agenda`) |
| POST   | `/api/marketing/reports`       | Submit report harian (`tanggal_report`, `deskripsi_progres`) |

### Leads (`auth:sanctum`, `role:leads`)

| Method | Endpoint                       | Deskripsi |
|--------|----------------------------------|-----------|
| GET    | `/api/leads/calendar`            | Agenda/reminder yang akan datang |
| GET    | `/api/leads/marketing-contact`   | Kontak marketing yang ditugaskan (nama & no. WA) |

---

## 9. Testing Cepat dengan cURL

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

## 10. Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `could not find driver (Connection: pgsql)` | Ekstensi `pdo_pgsql` belum aktif. Lihat bagian [4](#4-mengaktifkan-ekstensi-php-pdo_pgsql--pgsql). |
| `SQLSTATE[08006] could not connect to server` | Cek kembali `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` di `.env`, serta koneksi internet. |
| Response HTML 404/500 padahal endpoint API | Pastikan `routes/api.php` ter-load (cek `php artisan route:list`) dan request mengarah ke `/api/...`. |
| `Route [login] not defined` saat akses tanpa token | Sudah ditangani via custom exception handler di `bootstrap/app.php`, akan otomatis return JSON `401 Unauthenticated`. |
| Upload KTP tidak bisa diakses via URL | Jalankan `php artisan storage:link`. |
