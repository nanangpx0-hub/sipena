@echo off
REM =========================================================================
REM SI-PENA - Queue Worker Runner (Windows)
REM Menjalankan Laravel Database Queue Worker untuk job CompilePublicationJob.
REM
REM Pemakaian:
REM   1. Klik ganda berkas ini, ATAU
REM   2. Daftarkan sebagai layanan permanen (NSSM) agar otomatis nyala saat boot:
REM        nssm install SipenaQueue "C:\laragon\bin\php\php-8.2.32-nts-Win32-vs16-x64\php.exe" "C:\laragon\www\sipena\artisan" queue:work --sleep=3 --tries=3 --timeout=180
REM        nssm start SipenaQueue
REM =========================================================================
setlocal
cd /d C:\laragon\www\sipena

set PHP_BIN=C:\laragon\bin\php\php-8.2.32-nts-Win32-vs16-x64\php.exe

echo [SI-PENA] Menjalankan queue worker (tutup jendela ini untuk menghentikan)...
"%PHP_BIN%" artisan queue:work --sleep=3 --tries=3 --timeout=180

endlocal
