# SI-PENA (Sistem Penerbitan Angka)
**Satuan Kerja:** BPS Kabupaten Jember (Kode Wilayah 3509)  
**Tagline:** *"Goresan Angka Pasti untuk Masa Depan Jember."*  
**Akses Intranet/Lokal:** [http://sipena.test](http://sipena.test) atau `http://localhost:8000`

---

## 1. Ikhtisar Sistem
SI-PENA adalah sistem penerbitan dan penataan angka daerah berbasis web intranet untuk BPS Kabupaten Jember, dirancang dengan arsitektur **Tri-Language Separation**:
1. **PHP 8.2 (Laravel 11):** Web UI (Blade + Tailwind + Alpine.js), RBAC, manajemen publikasi, dan orkestrasi file.
2. **Python 3.10+ (CLI Engine):** Pembersihan data Excel OPD yang kotor (*merge cells*, baris kosong), agregasi data individu (Dapodik), kalkulasi matriks SKD (IKK, IPAK, Gap), dan plotting grafik SVG penduduk & iklim.
3. **Typst Standalone (`typst.exe` v0.15.1):** 100% *typesetting* dan perakitan PDF publikasi cetak standar BPS (KDA, DDA, SKD).

---

## 2. Cara Mengakses via `http://sipena.test`

Aplikasi telah dikonfigurasi pada Apache Laragon (Port 80 & 443 SSL):
- **Virtual Host:** `C:\laragon\etc\apache2\sites-enabled\auto.sipena.test.conf`
- **Document Root:** `C:\laragon\www\sipena\public`

### Langkah 1-Klik Mengaktifkan Domain:
Cukup jalankan berkas registrasi hosts:
1. Klik ganda berkas `register-sipena-domain.bat` yang terletak di:
   - `C:\laragon\www\register-sipena-domain.bat` atau
   - `C:\laragon\www\sipena\register-sipena-domain.bat`
2. Konfirmasi jendela izin Administrator (*User Account Control*).
3. Berkas akan otomatis menambahkan entri:
   ```text
   127.0.0.1      sipena.test  #laragon magic!
   ```
4. Browser akan otomatis terbuka ke [http://sipena.test](http://sipena.test).

*Catatan:* Anda juga bisa mengklik kanan ikon **Laragon** di system tray -> **Apache** -> **Reload**, maka Laragon akan otomatis menambahkan domain `sipena.test` ke file hosts.

---

## 3. Fitur Utama & Modul
- **Dashboard Eksekutif:** Monitoring 31 Kecamatan Dalam Angka (KDA), 1 Jember Dalam Angka (DDA), dan 1 Survei Kebutuhan Data (SKD).
- **Ingestion Data OPD:** Upload dan validasi otomatis berkas Excel dinas menggunakan Python worker.
- **Meja Redaksi & Editing:** Penyusunan narasi bab, tabel dinamis, dan verifikasi konsistensi data.
- **Kompilasi PDF Standar BPS (Typst):** Cetak publikasi sekelas InDesign/LaTeX dengan kecepatan milidetik.
- **Engine SKD & Analisis:** Analisis Kepuasan Konsumen (IKK, IPAK, Gap Analysis, Diagram Kartesius).
- **Desain Cover & Aset:** Personalisasi cover publikasi sesuai standar BPS.

---

## 4. Panduan Clone & Setup di PC Lain (Windows 11 / Laragon)

Jika Anda ingin melanjutkan pengerjaan di komputer/laptop lain, ikuti langkah berikut:

### Prasyarat di PC Baru:
- **Laragon** (atau Apache/MySQL + PHP 8.2+)
- **Composer** (manajer paket PHP)
- **Node.js 18+ & npm**
- **Python 3.10+** (tambahkan ke PATH saat instalasi)
- **Git**

### Langkah 1: Clone Repository
Buka terminal PowerShell atau CMD, lalu jalankan:
```bash
git clone https://github.com/nanangpx0-hub/sipena.git C:\laragon\www\sipena
cd C:\laragon\www\sipena
```

### Langkah 2: Setup Otomatis (1-Klik)
Cukup jalankan berkas batch yang sudah disediakan:
```bash
setup-pc-baru.bat
```
Script di atas akan otomatis:
1. Menyalin `.env.example` ke `.env`
2. Menjalankan `composer install` dan `php artisan key:generate`
3. Menjalankan `npm install` dan `npm run build`
4. Membuat virtual environment `python_engine\venv` dan menginstal pustaka Python (`requirements.txt`)
5. Menyiapkan storage symlink (`php artisan storage:link`)

### Langkah 3: Konfigurasi Database
1. Pastikan MySQL di Laragon aktif.
2. Buat database baru bernama `sipena` di phpMyAdmin / HeidiSQL / terminal MySQL.
3. Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` di `.env` jika database Anda menggunakan password.
4. Jalankan inisialisasi database:
   ```bash
   php artisan sipena:qa-reset
   ```
   *(Atau `php artisan migrate --seed` jika ingin data standar)*

### Langkah 4: Menjalankan Aplikasi
1. **Server Web:** Jalankan `php artisan serve --host=0.0.0.0:8000` (atau gunakan domain Laragon `http://sipena.test` via `register-sipena-domain.bat`).
2. **Queue Worker:** Klik ganda berkas `start-queue-worker.bat` untuk memproses tugas kompilasi Typst di latar belakang.
3. **Uji Validasi:** Jalankan `test.bat` untuk memastikan ketiga lapisan sistem (Python worker, backend Laravel, E2E) berfungsi 100% normal.

