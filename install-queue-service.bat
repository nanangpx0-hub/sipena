@echo off
REM =========================================================================
REM SI-PENA - Pemasangan Layanan Permanen Server (Windows 11 / Laragon)
REM
REM Komponen yang dipasang:
REM   1. Layanan NSSM  "sipena-queue-worker"  -> php artisan queue:work
REM      (menjalankan CompilePublicationJob secara permanen, nyala saat boot)
REM   2. Tugas Terjadwal "SI-PENA-Scheduler"  -> php artisan schedule:run
REM      (sekali sehari menjalankan scheduler Laravel, termasuk sipena:cleanup-temp)
REM
REM Pemakaian (jalankan MANUAL oleh admin server, bukan oleh AI agent):
REM   install-queue-service.bat install     Pasang layanan queue + tugas harian
REM   install-queue-service.bat uninstall   Cabut layanan + tugas harian
REM   install-queue-service.bat status      Cek status keduanya
REM   install-queue-service.bat schedule    Pasang tugas harian saja (tanpa NSSM)
REM
REM Deteksi otomatis: versi PHP Laragon tertinggi, proyek = folder skrip ini,
REM dan lokasi nssm.exe (folder skrip, PATH, atau Laragon).
REM =========================================================================
setlocal EnableExtensions
cd /d "%~dp0"

set "PROJECT_DIR=%~dp0"
if "%PROJECT_DIR:~-1%"=="\" set "PROJECT_DIR=%PROJECT_DIR:~0,-1%"
set "SERVICE_NAME=sipena-queue-worker"
set "SCHED_TASK=SI-PENA-Scheduler"
set "ACTION=%~1"
if "%ACTION%"=="" set "ACTION=status"

if not exist "%PROJECT_DIR%\artisan" (
    echo [SI-PENA][ERROR] Berkas artisan tidak ditemukan di "%PROJECT_DIR%".
    echo                  Jalankan skrip ini dari root proyek SI-PENA.
    exit /b 1
)

REM --- Deteksi PHP: PATH dulu, lalu Laragon (versi tertinggi), lalu fallback ---
set "PHP_BIN="
where php >nul 2>&1
if not errorlevel 1 (
    for /f "delims=" %%P in ('where php') do (
        if not defined PHP_BIN set "PHP_BIN=%%P"
    )
)

if not defined PHP_BIN (
    for %%L in (C D E F) do (
        if not defined PHP_BIN if exist "%%L:\laragon\bin\php" (
            for %%V in (8.5 8.4 8.3 8.2 8.1 8.0) do (
                if not defined PHP_BIN (
                    for /d %%D in ("%%L:\laragon\bin\php\php-%%V*") do (
                        if exist "%%D\php.exe" set "PHP_BIN=%%D\php.exe"
                    )
                )
            )
        )
    )
)

if not defined PHP_BIN (
    if exist "%PROJECT_DIR%\php.exe" set "PHP_BIN=%PROJECT_DIR%\php.exe"
)

if not defined PHP_BIN (
    echo [SI-PENA][ERROR] PHP tidak ditemukan. Pasang PHP 8.2+ di Laragon
    echo                  di C:\laragon\bin\php atau tambahkan php.exe ke PATH.
    exit /b 1
)
echo [SI-PENA] PHP      : %PHP_BIN%
echo [SI-PENA] Proyek   : %PROJECT_DIR%

REM --- Deteksi nssm.exe: folder skrip, PATH, lalu Laragon ---
set "NSSM_EXE="
if exist "%PROJECT_DIR%\nssm.exe" set "NSSM_EXE=%PROJECT_DIR%\nssm.exe"
if not defined NSSM_EXE if exist "%PROJECT_DIR%\bin\nssm.exe" set "NSSM_EXE=%PROJECT_DIR%\bin\nssm.exe"
if not defined NSSM_EXE (
    where nssm >nul 2>&1
    if not errorlevel 1 (
        for /f "delims=" %%N in ('where nssm') do (
            if not defined NSSM_EXE set "NSSM_EXE=%%N"
        )
    )
)
if not defined NSSM_EXE (
    for %%L in (C D E F) do (
        if not defined NSSM_EXE if exist "%%L:\laragon\bin\nssm\nssm.exe" set "NSSM_EXE=%%L:\laragon\bin\nssm\nssm.exe"
        if not defined NSSM_EXE if exist "%%L:\laragon\usr\nssm.exe" set "NSSM_EXE=%%L:\laragon\usr\nssm.exe"
    )
)

REM =========================================================================
REM INSTALL
REM =========================================================================
if /i "%ACTION%"=="install" (
    if not defined NSSM_EXE (
        echo [SI-PENA][ERROR] nssm.exe tidak ditemukan.
        echo                  Letakkan nssm.exe di "%PROJECT_DIR%\bin\nssm.exe"
        echo                  atau di PATH, lalu jalankan ulang skrip ini.
        exit /b 1
    )
    echo [SI-PENA] NSSM     : %NSSM_EXE%
    echo [SI-PENA] Memasang layanan "%SERVICE_NAME%"...

    "%NSSM_EXE%" install %SERVICE_NAME% "%PHP_BIN%" "%PROJECT_DIR%\artisan" queue:work --sleep=3 --tries=3 --timeout=180
    if errorlevel 1 (
        echo [SI-PENA][ERROR] Gagal memasang layanan. Bila sudah terpasang, jalankan:
        echo                  install-queue-service.bat uninstall
        exit /b 1
    )
    "%NSSM_EXE%" set %SERVICE_NAME% AppDirectory "%PROJECT_DIR%"
    "%NSSM_EXE%" set %SERVICE_NAME% DisplayName "SI-PENA Queue Worker (Laravel)"
    "%NSSM_EXE%" set %SERVICE_NAME% Description "Menjalankan CompilePublicationJob pada intranet BPS Kab. Jember."
    "%NSSM_EXE%" set %SERVICE_NAME% Start SERVICE_AUTO_START
    "%NSSM_EXE%" set %SERVICE_NAME% AppStdout "%PROJECT_DIR%\storage\logs\queue-worker.log"
    "%NSSM_EXE%" set %SERVICE_NAME% AppStderr "%PROJECT_DIR%\storage\logs\queue-worker.log"
    "%NSSM_EXE%" set %SERVICE_NAME% AppRotateFiles 1
    "%NSSM_EXE%" set %SERVICE_NAME% AppRotateBytes 5242880
    "%NSSM_EXE%" restart %SERVICE_NAME%
    if errorlevel 1 "%NSSM_EXE%" start %SERVICE_NAME%
    echo [SI-PENA] Layanan "%SERVICE_NAME%" terpasang dan dijalankan.

    call :install_schedule
    echo [SI-PENA] Selesai. Periksa dengan: install-queue-service.bat status
    exit /b 0
)

REM =========================================================================
REM UNINSTALL
REM =========================================================================
if /i "%ACTION%"=="uninstall" (
    if defined NSSM_EXE (
        "%NSSM_EXE%" stop %SERVICE_NAME% >nul 2>&1
        "%NSSM_EXE%" remove %SERVICE_NAME% confirm
        echo [SI-PENA] Layanan "%SERVICE_NAME%" dicabut.
    ) else (
        echo [SI-PENA][WARN] nssm.exe tidak ditemukan; layanan dilewati.
        sc query %SERVICE_NAME% >nul 2>&1 && sc delete %SERVICE_NAME%
    )
    schtasks /Delete /TN "%SCHED_TASK%" /F >nul 2>&1
    if errorlevel 1 (
        echo [SI-PENA][WARN] Tugas terjadwal "%SCHED_TASK%" tidak ada.
    ) else (
        echo [SI-PENA] Tugas terjadwal "%SCHED_TASK%" dihapus.
    )
    exit /b 0
)

REM =========================================================================
REM SCHEDULE SAJA
REM =========================================================================
if /i "%ACTION%"=="schedule" (
    call :install_schedule
    exit /b 0
)

REM =========================================================================
REM STATUS (default)
REM =========================================================================
if defined NSSM_EXE (
    echo [SI-PENA] ---- Status layanan queue ----
    "%NSSM_EXE%" status %SERVICE_NAME%
) else (
    sc query %SERVICE_NAME% >nul 2>&1 && (
        echo [SI-PENA] ---- Status layanan queue ----
        sc query %SERVICE_NAME%
    ) || (
        echo [SI-PENA] Layanan "%SERVICE_NAME%" belum terpasang.
    )
)
echo.
echo [SI-PENA] ---- Status scheduler harian ----
schtasks /Query /TN "%SCHED_TASK%" /FO LIST /V 2>nul || echo [SI-PENA] Tugas terjadwal "%SCHED_TASK%" belum terpasang.
exit /b 0

REM =========================================================================
REM Subroutine: pasang tugas harian scheduler Laravel (jam 00.10 WIB)
REM =========================================================================
:install_schedule
set "SCH_CMD=%PHP_BIN% %PROJECT_DIR%\artisan schedule:run"
echo [SI-PENA] Memasang tugas terjadwal harian "%SCHED_TASK%" (00.10)...
schtasks /Create /TN "%SCHED_TASK%" /TR "%SCH_CMD%" /SC DAILY /ST 00:10 /F >nul
if errorlevel 1 (
    echo [SI-PENA][ERROR] Gagal memasang tugas terjadwal. Jalankan skrip ini
    echo                  dari Command Prompt Administrator.
    exit /b 1
)
echo [SI-PENA] Tugas terjadwal "%SCHED_TASK%" terpasang.
exit /b 0
