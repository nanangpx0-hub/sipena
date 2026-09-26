# AGENTS.md
## Panduan Operasional AI Coding Agent (SI-PENA)
**Proyek:** SI-PENA (Sistem Penerbitan Angka) - BPS Kabupaten Jember  
**Target Pengguna Agent:** Cline, Kilo Code, Kiro Code, Antigravity, Open Code  
**Model Rekomendasi:** Google Gemini 2.0 / 1.5 Flash (Free Tier), GitHub Models (GPT-4o-mini / Llama-3.3-70B), Groq  
**Host Environment:** Windows 11 Pro 64-bit (Intranet Server)  

---

## 1. Aturan Mutlak & Batasan Sistem (Non-Negotiable Constraints)

Sebagai AI Agent, Anda **WAJIB** mematuhi aturan arsitektur berikut dalam setiap pembuatan dan modifikasi kode:

1. **Lingkungan Host Windows 11:**
   * **DILARANG** mengasumsikan perintah terminal Linux/Bash (seperti `sudo`, `chmod`, `chown`, `apt-get`, `grep`, `cat`, atau path `/var/www/`).
   * Gunakan sintaks shell PowerShell atau Windows Command Prompt (`cmd.exe`).
   * Penulisan path pada string PHP dan Python wajib menggunakan pemisah direktori yang aman: gunakan konstanta `DIRECTORY_SEPARATOR`, forward slash (`/`), atau double-backslash (`C:\\laragon\\www\\sipena\\...`).

2. **Pemisahan Tanggung Jawab Tiga Bahasa (Strict Tri-Language Separation):**
   * **PHP 8.2 (Laravel 11):** Menangani routing HTTP, antarmuka web (Blade/Tailwind), autentikasi RBAC, manajemen deadline/status bab, transaksi database MySQL 8, dan orkestrasi file. **DILARANG** melakukan komputasi matriks survei berat atau merender grafik secara native di PHP.
   * **Python 3.11+ (CLI Worker):** Menangani pembersihan data Excel dinas yang kotor (*merge cells*, baris kosong), agregasi data individu (sekolah/Dapodik), kalkulasi matriks survei SKD (IKK, IPAK, Gap), dan plotting grafik SVG (piramida penduduk, curah hujan, diagram kartesius). **DILARANG** menghasilkan output HTML; Python hanya berkomunikasi via stream JSON melalui stdout.
   * **Typst CLI (`typst.exe`):** Menangani 100% *typesetting* dan perakitan PDF akhir. **DILARANG** menggunakan library generator PDF berbasis PHP (seperti DOMPDF, FPDF, atau Snappy) atau modul PDF Python. Semua dokumen PDF wajib dikompilasi oleh `typst.exe`.

3. **Efisiensi Kuota Token Gratis (Free Tier Token Preservation):**
   * Jangan pernah mencoba membangun seluruh aplikasi dalam satu prompt besar.
   * Kerjakan kode modul per modul secara terisolasi (*context slicing*).
   * Utamakan kode yang modular, minim dependensi luar yang tidak esensial, dan langsung berfungsi (*production-ready*).

4. **Integritas Angka (Integrity of Official Numbers):**
   * **DILARANG** menampilkan angka tebakan, *hardcode*, atau data contoh sebagai angka resmi. Bila worker Python gagal atau metrik SKD belum ada, sistem wajib menampilkan status "belum ada data" dan membatalkan kompilasi, bukan mengisi angka kosong.

---

## 2. Struktur Lingkungan Kerja di Server Windows 11

Pastikan kode yang Anda buat merujuk pada struktur path baku berikut:

```text
C:\laragon\www\sipena\
├── app\                                # Laravel 11 Backend
├── python_engine\                      # Worker Analitis Python
│   ├── venv\Scripts\python.exe         # Interpreter Python terisolasi
│   ├── parsers\                        # Script ekstraksi Excel OPD
│   ├── calculators\                    # Script formula SKD
│   ├── visualizers\                    # Script render grafik SVG Matplotlib
│   ├── requirements.txt                # Dependensi pip (pytest termasuk di dalamnya)
│   └── tests\                          # Unit test pytest (python_engine\tests)
├── typst_engine\                       # Typesetting Engine
│   ├── bin\typst.exe                   # Binary compiler Typst
│   ├── fonts\                          # Font cetak tersimpan di repo (Roboto, OFL)
│   └── templates\                      # Template master KDA, DDA, SKD
├── tests\
│   ├── Feature\                        # Feature test Laravel (phpunit, sqlite in-memory)
│   ├── Concerns\                       # Trait pembantu pengujian (mis. BuildsXlsx)
│   └── e2e\                            # E2E HTTP 7 suite (test_e2e_sipena.py)
└── storage\
    ├── app\private\raw_excel\          # Arsip berkas asli OPD berdasar versi SHA-256
    ├── app\private\custom_assets\      # Cover kustom hasil unggah
    ├── custom_assets\                  # SVG hasil render (piramida, iklim, kartesius)
    ├── output_pdf\                     # PDF draf dan siap rilis
    └── temp\                           # Berkas .typ sementara & artefak E2E
```

---

## 3. Perintah Verifikasi Wajib (Sebelum Melapor Selesai)

| Jenis | Perintah |
| --- | --- |
| Gaya kode PHP | `vendor\bin\pint --test` (perbaiki dengan `vendor\bin\pint`) |
| Feature test backend | `php artisan test` (sqlite in-memory, tanpa MySQL) |
| Unit test worker Python | `python_engine\venv\Scripts\python.exe -m pytest python_engine\tests -q` |
| Build aset lokal | `npm run build` (Tailwind + Alpine + font self-host) |
| E2E penuh (server hidup) | `php artisan sipena:qa-reset` lalu `python_engine\venv\Scripts\python.exe tests\e2e\test_e2e_sipena.py` |
| Satu pintu | `test.bat` (menjalankan ketiga lapisan pengujian) |

Server pengembangan: `php artisan serve --host=0.0.0.0:8000` dan antrean `start-queue-worker.bat`.

---

## 4. Aturan Git

* Repository diinisialisasi dengan branch `main`; seluruh perubahan kode wajib lolos ketiga lapis pengujian di atas sebelum *commit*.
* Jangan pernah meng-*commit* `.env`, isi `storage\output_pdf`, `storage\temp`, `storage\app\private\raw_excel`, `vendor\`, `node_modules\`, dan `public\build\` (semuanya sudah ada di `.gitignore`).
* Jangan pernah meng-*commit* kredensial database atau API key.
