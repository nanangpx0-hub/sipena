# ARCHITECTURE.md
## Cetak Biru Arsitektur Sistem SI-PENA (Sistem Penerbitan Angka)
**Satuan Kerja:** BPS Kabupaten Jember  
**Lingkungan Operasional:** PC Server Intranet (Host: Windows 11 Pro 64-bit)  
**Teknologi Utama:** PHP 8.2 (Laravel 11), MySQL 8.0, Python 3.11+, Typst CLI Engine  

---

## 1. Topologi Sistem & Infrastruktur Jaringan

Sistem dibangun dengan arsitektur **Monolit Modular Terisolasi (*Modular Monolith with CLI Workers*)** yang dipasang pada satu unit PC Workstation Windows 11 di kantor BPS Kabupaten Jember. Seluruh laptop pegawai mengakses sistem melalui antarmuka web via browser pada jaringan LAN/WiFi intranet kantor tanpa perlu terhubung ke internet publik.

```text
                  [ Laptop Klien Pegawai / Pimpinan BPS Jember ]
                                         │
                                         │ (Protokol HTTP via Port 8000)
                                         ▼
┌───────────────────────────────────────────────────────────────────────────────┐
│                    SERVER WORKSTATION (WINDOWS 11 PRO)                        │
│                                                                               │
│  [Web Server Gateway]                                                         │
│  • Nginx for Windows / Apache HTTP Server (Binding: 192.168.x.x:8000)         │
│                                                                               │
│  [Aplikasi Inti: PHP 8.2 / Laravel 11 Runtime]                                │
│  • Antarmuka Pengguna (Blade, Tailwind CSS, Alpine.js)                        │
│  • Autentikasi & Otorisasi RBAC (Operator, Editor, Approver, Viewer)          │
│  • State Machine & Status Locking Bab Publikasi                               │
│  • Modul Ingesti Data & Fuzzy Matching Wilayah                                │
│  • Editor Teks Ulasan Bilingual Berbasis Token (ID/EN)                        │
│  • Orkestrator Template Typst Markup                                          │
│  • Antrean Background Job (Laravel Database Queue)                            │
│                                                                               │
│  [Komunikasi Antar-Proses: Symfony Process Component]                         │
│             │                                            │                    │
│             ▼ (CLI Exec)                                 ▼ (CLI Exec)         │
│  [Python 3.11+ Worker Engine]                 [Typst Standalone Compiler]     │
│  • python_engine\venv\Scripts\python.exe   • typst_engine\bin\typst.exe       │
│  • Pandas / OpenPyXL (Pembersih Excel OPD)     • Master Layout Engine A5 / B5 │
│  • Agregator Baris Individu (Dapodik/EMIS)    • Generator Cover & Divider     │
│  • Kalkulator Matriks VKD (IKK, IPAK, Gap)    • Output: PDF Cetak & Web       │
│  • Matplotlib (SVG Piramida & Curah Hujan)                                    │
│                                                                               │
│  [Penyimpanan Data Terstruktur: MySQL 8.0]                                    │
│  • Default Port 3306, InnoDB Engine, utf8mb4_unicode_ci                       │
│  • JSON Datatype Support untuk skema pemetaan fleksibel                       │
│                                                                               │
│  [Sistem Berkas Windows (NTFS Storage)]                                       │
│  • storage\app\private\raw_excel\ (Arsip Audit Versioning SHA-256)            │
│  • storage\app\private\custom_assets\ (Cover & Peta Kustom Manual)            │
│  • storage\output_pdf\ (Hasil Kompilasi PDF Final/Draft)                      │
└───────────────────────────────────────────────────────────────────────────────┘