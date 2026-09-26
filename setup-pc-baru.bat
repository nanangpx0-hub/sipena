@echo off
setlocal enabledelayedexpansion
title Setup Lingkungan Kerja SI-PENA (PC Baru)
color 0B
cd /d "%~dp0"

echo =========================================================================
echo   SI-PENA (Sistem Penerbitan Angka) - BPS Kabupaten Jember
echo   Otomatisasi Instalasi & Penyiapan Lingkungan Kerja di PC Baru
echo =========================================================================
echo.

REM 1. Cek berkas konfigurasi .env
echo [1/6] Memeriksa berkas konfigurasi .env...
if not exist .env (
    echo [*] Menyalin .env.example menjadi .env...
    copy .env.example .env >nul
    echo [OK] Berkas .env berhasil dibuat dari template.
) else (
    echo [OK] Berkas .env sudah ada.
)

REM 2. Dependensi PHP via Composer
echo.
echo [2/6] Memasang dependensi PHP (Composer)...
call composer install --no-interaction
if %errorlevel% neq 0 (
    echo [GAGAL] Composer install gagal. Pastikan Composer dan PHP sudah terpasang di PC ini.
    goto :selesai
)
echo [*] Generate APP_KEY Laravel...
call php artisan key:generate --force
echo [*] Membuat storage symlink...
call php artisan storage:link

REM 3. Dependensi Frontend & Build Aset
echo.
echo [3/6] Memasang dependensi Node.js & kompilasi aset frontend...
call npm install --no-audit
if %errorlevel% neq 0 (
    echo [PERINGATAN] npm install mengalami kendala. Pastikan Node.js terpasang.
) else (
    call npm run build
    echo [OK] Aset frontend berhasil dikompilasi (public/build).
)

REM 4. Virtual Environment Python Worker
echo.
echo [4/6] Menyiapkan environment Python analitis (python_engine)...
if not exist python_engine\venv (
    echo [*] Membuat virtual environment Python baru...
    python -m venv python_engine\venv
    if %errorlevel% neq 0 (
        echo [GAGAL] Gagal membuat virtual environment Python. Pastikan Python 3.10+ terpasang di PC ini.
        goto :selesai
    )
) else (
    echo [OK] Virtual environment python_engine\venv sudah ada.
)

echo [*] Memasang pustaka Python (pandas, openpyxl, matplotlib, pytest)...
python_engine\venv\Scripts\python.exe -m pip install --upgrade pip
python_engine\venv\Scripts\python.exe -m pip install -r python_engine\requirements.txt
if %errorlevel% neq 0 (
    echo [PERINGATAN] Pemasangan dependensi pip ada yang gagal. Periksa koneksi internet Anda.
) else (
    echo [OK] Seluruh pustaka Python worker berhasil dipasang.
)

REM 5. Verifikasi Typst Engine
echo.
echo [5/6] Memverifikasi Typst Standalone Compiler...
if exist typst_engine\bin\typst.exe (
    typst_engine\bin\typst.exe --version
    echo [OK] Typst compiler siap digunakan.
) else (
    echo [GAGAL] typst_engine\bin\typst.exe tidak ditemukan!
)

REM 6. Panduan Database
echo.
echo =========================================================================
echo   [6/6] PENYIAPAN SELESAI
echo =========================================================================
echo.
echo   Langkah Terakhir (Database):
echo   1. Pastikan database MySQL (Laragon/XAMPP) menyala.
echo   2. Buat database baru bernama 'sipena' (atau sesuaikan DB_DATABASE di .env).
echo   3. Sesuaikan DB_USERNAME dan DB_PASSWORD di file .env jika diperlukan.
echo   4. Jalankan migrasi dan data awal (pilih salah satu):
echo        - Data standar : php artisan migrate --seed
echo        - Data QA demo : php artisan sipena:qa-reset
echo   5. Jalankan pengujian 3 lapis untuk verifikasi:
echo        test.bat
echo   6. Jalankan server:
echo        php artisan serve --host=0.0.0.0:8000
echo        start-queue-worker.bat
echo =========================================================================
echo.

:selesai
pause
