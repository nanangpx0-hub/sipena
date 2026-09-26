@echo off
setlocal enabledelayedexpansion
title Registrasi Domain Lokal sipena.test - SI-PENA BPS Jember
color 0A

:: Cek hak akses Administrator
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo ======================================================================
    echo   SI-PENA (Sistem Penerbitan Angka) - BPS Kabupaten Jember
    echo ======================================================================
    echo   Meminta izin Administrator untuk mendaftarkan domain sipena.test...
    echo.
    powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b
)

cls
echo ======================================================================
echo   SI-PENA (Sistem Penerbitan Angka) - BPS Kabupaten Jember
echo   Konfigurasi Domain Lokal Intranet: sipena.test
echo ======================================================================
echo.

set HOSTS_FILE=%WINDIR%\System32\drivers\etc\hosts

:: Periksa apakah sipena.test sudah terdaftar
findstr /i /c:"sipena.test" "%HOSTS_FILE%" >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] sipena.test sudah terdaftar di Windows hosts file!
) else (
    echo [*] Menambahkan entri sipena.test ke %HOSTS_FILE%...
    echo.>> "%HOSTS_FILE%"
    echo 127.0.0.1      sipena.test  #laragon magic!>> "%HOSTS_FILE%"
    echo [SUKSES] sipena.test berhasil ditambahkan ke hosts file!
)

:: Flush DNS Resolver Cache
echo [*] Menyegarkan cache DNS lokal (ipconfig /flushdns)...
ipconfig /flushdns >nul 2>&1
echo [OK] Cache DNS berhasil disegarkan.
echo.

echo ======================================================================
echo   Verifikasi Akses:
echo   - Domain       : http://sipena.test
echo   - Virtual Host : Apache 2.4 (Port 80 / 443)
echo   - Document Root: C:\laragon\www\sipena\public
echo ======================================================================
echo.
echo Membuka http://sipena.test di browser...
start http://sipena.test

echo.
echo Tekan tombol apa saja untuk menutup jendela ini...
pause >nul
