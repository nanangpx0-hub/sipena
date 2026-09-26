# MODUL 01: ARSITEKTUR DAN DESAIN SISTEM
## SI-PENA (Sistem Penerbitan Angka) - BPS Kabupaten Jember

---

## 1. Desain Arsitektur Tri-Language Terisolasi

SI-PENA dibangun di atas prinsip pemisahan tanggung jawab berbasis tiga bahasa pemrograman (*Strict Tri-Language Separation*). Prinsip ini memastikan bahwa setiap teknologi hanya menangani tugas yang paling optimal baginya:

```text
+-----------------------------------------------------------------------------------+
|                            SI-PENA WORKSTATION SERVER                             |
+-----------------------------------------------------------------------------------+
                                          |
        +---------------------------------+---------------------------------+
        |                                                                   |
        v                                                                   v
+-------------------------------+                         +---------------------------------+
|     PHP 8.2 (Laravel 11)      |                         |      MySQL 8.0 Database         |
|  - Web UI (Blade + Tailwind)  |<----------------------->|  - InnoDB Storage Engine        |
|  - Routing & RBAC Middleware  |                         |  - JSON Data Columns            |
|  - State Machine & Validation |                         |  - UTF8MB4 Collation            |
+-------------------------------+                         +---------------------------------+
        |
        | IPC via Symfony Process
        +---------------------------------+
        |                                 |
        v                                 v
+-------------------------------+ +---------------------------------+
|   Python 3.10+ CLI Worker     | |    Typst Standalone Compiler    |
| - Excel Cleaner & Pandas      | | - Master Templates (A5 & B5)    |
| - Fuzzy Matching Kewilayahan  | | - High Precision Vector Tables  |
| - Formula SKD (IKK, IPAK, Gap)| | - Native PDF Output Engine      |
| - Matplotlib SVG Charts       | | - Sub-second Execution          |
+-------------------------------+ +---------------------------------+
```

### 1.1 Standar Komunikasi Antar-Proses (IPC)
1. **PHP ke Python:**
   - PHP mengeksekusi interpreter Python terisolasi: `python_engine/venv/Scripts/python.exe`.
   - Argumen diteruskan melalui flag baris perintah CLI.
   - Python memproses komputasi secara murni dan mengirimkan balasan berformat **JSON string terstruktur melalui stdout**.
   - PHP menangkap stdout, memverifikasi status return code (`0` = sukses), dan mem-parsing output JSON menjadi array asosiatif.

2. **PHP ke Typst CLI:**
   - PHP merakit file markup Typst sementara (`.typ`) pada direktori `storage/temp/`.
   - File sementara mengimpor template master (`kda_master.typ`, `dda_master.typ`, atau `skd_master.typ`).
   - PHP memanggil `typst_engine/bin/typst.exe compile <input.typ> <output.pdf> --root <project_root>`.
   - Typst menyusun dokumen PDF secara langsung dalam hitungan ratusan milidetik.

---

## 2. Struktur Direktori Baku Sistem

```text
C:\laragon\www\sipena\
├── app\
│   ├── Http\
│   │   ├── Controllers\        # Controller antarmuka per modul
│   │   └── Middleware\         # Validasi batas waktu (CheckDeadline)
│   ├── Jobs\                   # Background jobs (CompilePublicationJob)
│   ├── Models\                 # Eloquent ORM Models
│   └── Services\               # Business logic (FuzzyMatchService)
├── database\
│   ├── migrations\             # 5 skema migrasi tabel
│   └── seeders\                # Seeder 31 kecamatan, roles, dan publications
├── docs\                       # Dokumentasi teknis modular
├── python_engine\              # Worker Analitis Python
│   ├── venv\                   # Virtual environment terisolasi
│   ├── parsers\                # generic_cleaner.py, individual_aggregator.py
│   ├── calculators\            # skd_engine.py
│   └── visualizers\            # population_pyramid.py, climate_chart.py
├── resources\
│   └── views\                  # Blade template (layouts, dashboard, ingestion, dll)
├── storage\
│   ├── raw_excel\              # Arsip berkas asli OPD berdasar SHA-256
│   ├── custom_assets\          # Foto cover dan peta kustom
│   └── output_pdf\             # Berkas PDF hasil kompilasi final
└── typst_engine\               # Typesetting Engine
    ├── bin\typst.exe           # Binary compiler Typst v0.15.1
    └── templates\              # Master template kda, dda, skd, dan komponen
```
