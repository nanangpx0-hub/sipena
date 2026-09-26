# MODUL 03: PANDUAN INSTALASI & DEVOPS WINDOWS 11
## SI-PENA (Sistem Penerbitan Angka) - BPS Kabupaten Jember

Panduan ini berisi petunjuk lengkap bagi Administrator Sistem dan DevOps untuk mengatur, menjalankan, dan merawat server SI-PENA di lingkungan Windows 11 Pro 64-bit.

---

## 1. Persyaratan Perangkat Lunak (Prerequisites)

1. **Laragon Full Edition:**
   * Apache 2.4.54 (Win64)
   * PHP 8.2 (ekstensi aktif: `pdo_mysql`, `mbstring`, `curl`, `gd`, `openssl`, `fileinfo`, `zip`)
   * MySQL 8.0.30
2. **Python 3.10+ (64-bit):**
   * Terpasang pada `C:\laragon\bin\python\` atau sistem PATH Windows.
3. **Typst Standalone Compiler:**
   * Versi 0.15.1 atau lebih baru di `C:\laragon\www\sipena\typst_engine\bin\typst.exe`.

---

## 2. Langkah Konfigurasi Lingkungan

### 2.1 Konfigurasi Virtual Host Apache
Buat berkas: `C:\laragon\etc\apache2\sites-enabled\auto.sipena.test.conf`

```apache
<VirtualHost *:80>
    DocumentRoot "C:/laragon/www/sipena/public"
    ServerName sipena.test
    ServerAlias *.sipena.test
    <Directory "C:/laragon/www/sipena/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

<VirtualHost *:443>
    DocumentRoot "C:/laragon/www/sipena/public"
    ServerName sipena.test
    ServerAlias *.sipena.test
    SSLEngine on
    SSLCertificateFile "C:/laragon/etc/ssl/laragon.crt"
    SSLCertificateKeyFile "C:/laragon/etc/ssl/laragon.key"

    <Directory "C:/laragon/www/sipena/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 2.2 Pendaftaran Domain Lokal `sipena.test`
Jalankan berkas registrasi 1-klik:
`C:\laragon\www\register-sipena-domain.bat`
Atau klik kanan ikon Laragon tray -> **Apache** -> **Reload**.

### 2.3 Konfigurasi Environment Laravel (`.env`)
Salin berkas `.env.example` ke `.env`, sesuaikan parameter:
```env
APP_NAME="SI-PENA"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://sipena.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipena
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
```

Jalankan perintah inisialisasi:
```powershell
cd C:\laragon\www\sipena
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
```

---

## 3. Setup Lingkungan Python Worker

Masuk ke folder `python_engine` dan buat virtual environment:
```powershell
cd C:\laragon\www\sipena\python_engine
python -m venv venv
.\venv\Scripts\pip.exe install pandas openpyxl matplotlib numpy
```

---

## 4. Menjalankan Antrean Background (Queue Worker)

Untuk memproses kompilasi batch 31 publikasi KDA di latar belakang:
```powershell
php artisan queue:work --tries=3 --timeout=300
```
Untuk produksi workstation, worker ini dapat dijalankan sebagai layanan atau skrip latar belakang.

---

## 5. Pemeriksaan Kesehatan Sistem (Health Check)

Jalankan pengujian end-to-end melalui PowerShell:
```powershell
& "C:\laragon\www\sipena\python_engine\venv\Scripts\python.exe" "C:\laragon\www\sipena\python_engine\test_e2e_sipena.py"
```
Jika seluruh pengujian menghasilkan tanda `[OK]`, sistem telah 100% siap operasional.
