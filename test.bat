@echo off
REM =========================================================================
REM  PENGUJI OTOMATIS SI-PENA
REM  1. pytest  -> unit worker Python (pembersih Excel, engine SKD)
REM  2. phpunit -> feature test backend (sqlite in-memory, tanpa DB MySQL)
REM  3. E2E     -> alur HTTP penuh (butuh server: php artisan serve :8000)
REM  Jalankan dari root proyek:  test.bat  atau  .\test.bat
REM =========================================================================
setlocal
cd /d "%~dp0"
set FAILED=0

echo.
echo === [1/3] pytest - worker Python =====================================
python_engine\venv\Scripts\python.exe -m pytest python_engine\tests -q
if errorlevel 1 set FAILED=1

echo.
echo === [2/3] phpunit - backend Laravel ==================================
php artisan test
if errorlevel 1 set FAILED=1

echo.
echo === [3/3] E2E HTTP - 7 suite kritis ==================================
REM Cek server agar E2E tidak gagal membingungkan
curl -s -o nul -w "server check: %%{http_code}\n" http://127.0.0.1:8000/login
if errorlevel 1 (
    echo SERVER TIDAK BERJALAN. Jalankan dulu:  php artisan serve --host=0.0.0.0:8000
    set FAILED=1
    goto :selesai
)
REM Reset fixture QA agar ingesti E2E selalu mulai dari PENDING_DATA
php artisan sipena:qa-reset
python_engine\venv\Scripts\python.exe tests\e2e\test_e2e_sipena.py
if errorlevel 1 set FAILED=1

:selesai
echo.
if "%FAILED%"=="1" (
    echo HASIL: ADA KEGAGALAN - periksa log di atas.
    exit /b 1
)
echo HASIL: SEMUA LULUS.
exit /b 0
