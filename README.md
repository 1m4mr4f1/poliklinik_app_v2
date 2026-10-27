# Poliklinik App v2

Aplikasi manajemen poliklinik untuk tugas Fast Track Bengkel Koding, dibangun dengan Laravel 13. Lingkup fitur dan kriteria tugas mengikuti [SOAL.md](./SOAL.md); dasar alur aplikasi mengikuti [panduan-tugas-poliklinik.md](./panduan-tugas-poliklinik.md).

## Kebutuhan

- PHP 8.3+
- Composer
- MySQL
- Node.js dan npm

## Persiapan lokal

1. Buat database MySQL bernama `polipoli`.
2. Salin `.env.example` menjadi `.env`, lalu isi kredensial database lokal:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=polipoli
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. Pasang dependency, siapkan application key, migrasikan database, dan build aset:

   ```bash
   composer install
   php artisan key:generate
   php artisan reverb:install
   php artisan migrate
   npm install
   npm run build
   ```

4. Jalankan aplikasi dan Vite saat pengembangan:

   ```bash
   php artisan serve
   npm run dev
   ```

5. Jalankan server WebSocket Reverb di terminal lain:

   ```bash
   php artisan reverb:start
   ```

Port Reverb development default adalah `18081` untuk menghindari konflik dengan service lokal lain. Jika diubah, samakan `REVERB_PORT` dan `REVERB_SERVER_PORT` di `.env`.

## Pengujian

Tes fitur database memakai MySQL terpisah bernama `polipoli_test`, agar tidak menghapus atau mengubah data development. Buat database tersebut terlebih dahulu, lalu jalankan:

```bash
DB_CONNECTION=mysql DB_DATABASE=polipoli_test php artisan test
```
