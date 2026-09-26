# ROADMAP.md
## Rencana Aksi Pengembangan & Peluncuran SI-PENA
**Sistem Penerbitan dan Penataan Angka Daerah**  
**Satuan Kerja:** BPS Kabupaten Jember (Kode Satker: 3509)  
**Target Go-Live:** Januari 2027  
**Durasi Efektif:** Oktober 2026 – Januari 2027 (16 Pekan Kerja)  
**Host Platform:** PC Server Intranet (Windows 11 Pro 64-bit)  

---

## 1. Sasaran Strategis & Tonggak Utama (High-Level Milestones)

Roadmap ini dirancang untuk memandu pembangunan dan transisi bertahap dari penyusunan publikasi manual berbasis Adobe InDesign menuju sistem otomasi terpadu SI-PENA. Target utamanya adalah sistem telah teruji penuh dan resmi digunakan untuk menyusun publikasi **Kabupaten Jember Dalam Angka (DDA) 2027** pada awal tahun 2027[cite: 2].

| Periode | Fase | Fokus Utama | Target Deliverable |
| :--- | :--- | :--- | :--- |
| **Oktober 2026** | **Fase 1** | Fondasi Arsitektur, Basis Data, & Typst Master | Skema MySQL 8, RBAC, master wilayah, serta template cetak Typst A5/B5 tervalidasi[cite: 1, 2, 3]. |
| **November 2026** | **Fase 2** | Mesin Ingesti Data, Worker Python, & Narasi Dinamis | Parser Excel kotor OPD, kalkulator analitik SKD, dan editor teks bilingual[cite: 1, 2, 3]. |
| **Desember 2026** | **Fase 3** | Workflow Locking, Antrean Kompilasi Masal, & UAT | State machine, kompilasi masal 31 KDA, simulasi data riil 2026, dan gladi bersih tim[cite: 1, 2]. |
| **Januari 2027** | **Fase 4** | **Deployment Produksi & Official Go-Live** | Server Windows 11 aktif di LAN, bimtek staf, langsung dipakai untuk DDA 2027[cite: 2]. |

---

## 2. Rincian Rencana Aksi Mingguan (Weekly Breakdown)

### BULAN 1: OKTOBER 2026 — FONDASI ARSITEKTUR & TEMPLATE CETAK

#### Pekan 1: Lingkungan Server & Skema Basis Data MySQL 8
* [ ] Setup lingkungan kerja pada PC server Windows 11 (instalasi Laragon / PHP 8.2, MySQL 8.0, dan Git).
* [ ] Inisialisasi repositori proyek Laravel 11.
* [ ] Eksekusi migrasi tabel basis data sesuai spesifikasi `ARCHITECTURE.md`:
  * Tabel otorisasi: `roles` dan `users` (RBAC: Operator, Editor, Approver, Viewer).
  * Tabel master wilayah: `districts` dan `villages`.
  * Tabel manajemen berkas: `publications`, `raw_data_files`, dan `workflow_logs`.
* [ ] Eksekusi seeder master resmi: 31 Kecamatan dan 248 Desa/Kelurahan Kabupaten Jember[cite: 2].
* [ ] Pembuatan akun default untuk 4 peran pengguna.

#### Pekan 2: Konfigurasi Subproses Python & Binary Typst
* [ ] Instalasi Python 3.11/3.12 64-bit pada server Windows 11.
* [ ] Pembentukan Python Virtual Environment terisolasi di `python_engine/venv/`.
* [ ] Instalasi pustaka data sains Python: `pandas`, `openpyxl`, `matplotlib`, `seaborn`, `scipy`.
* [ ] Peletakan binary mandiri `typst.exe` pada direktori `typst_engine/bin/`.
* [ ] Pembangunan `PythonWorkerService.php` dan `TypstCompilerService.php` di Laravel menggunakan komponen `symfony/process`.
* [ ] Pengujian eksekusi subproses dasar: PHP berhasil menjalankan perintah CLI Python dan Typst di Windows 11.

#### Pekan 3: Komponen Cetak Typst Master (Tabel & Pembatas Bab)
* [ ] Pembuatan modul `typst_engine/templates/components/tables.typ`:
  * Implementasi *three-line table* resmi BPS (garis atas tebal 1.2pt, garis bawah header 0.8pt, garis penutup 1.2pt, tanpa garis vertikal)[cite: 1, 2].
  * Baris penomoran kolom `(1), (2), (3)...` di bawah kepala kolom[cite: 1, 2].
  * Mekanisme otomatis sambungan tabel (*continued table*): pencetakan otomatis *"Lanjutan Tabel / Continued Table"* dan pengulangan header kolom saat tabel berpindah halaman[cite: 1, 2].
* [ ] Pembuatan modul `typst_engine/templates/components/divider.typ`:
  * Tata letak lembar pembatas bab dengan nomor bab besar (54pt) beraksen oranye BPS (`#E67E22`)[cite: 1, 2].
  * Judul bab bilingual sejajar (Bahasa Indonesia di atas, Bahasa Inggris di bawah)[cite: 1, 2].
  * Kotak statistik kunci (*key metrics highlight*) dinamis dengan bingkai tipis dan latar `#F8F9FA`[cite: 1, 2].

#### Pekan 4: Perakitan Master Layout KDA, DDA, & SKD
* [ ] Pembuatan template master `kda_master.typ` (Ukuran A5: $14,8 \times 21,0\text{ cm}$)[cite: 1]:
  * Konfigurasi margin cetak bolak-balik: Dalam (*gutter*) $2,2\text{ cm}$, Luar $1,8\text{ cm}$, Atas/Bawah $2,0\text{ cm}$.
  * Tata letak ulasan narasi dua kolom berdampingan (*ULASAN* di kiri, *DESCRIPTION* di kanan)[cite: 1].
  * Penomoran halaman: Angka Romawi kecil pada prelims dan angka Arabik pada bab isi[cite: 1].
* [ ] Pembuatan template master `dda_master.typ` (Ukuran B5/A5 dengan margin dalam $2,5\text{ cm}$)[cite: 2].
* [ ] Pembuatan template master `skd_master.typ` (Ukuran B5: $18,2 \times 25,7\text{ cm}$)[cite: 3].
* [ ] Uji coba kompilasi dummy: menghasilkan draf PDF buku lengkap tanpa error layout.

---

### BULAN 2: NOVEMBER 2026 — INGESTI DATA, ANALITIK SKD & NARASI DINAMIS

#### Pekan 5: Ingesti Data Rekapitulasi & Versioning Berkas Mentah OPD
* [ ] Pembangunan antarmuka upload file Excel dinas/OPD untuk peran Operator.
* [ ] Penerapan mekanisme kalkulasi hash SHA-256 pada setiap berkas yang diunggah.
* [ ] Penerapan skema versioning penyimpanan:  
  `/storage/raw_excel/{tahun}/{instansi}/{timestamp}_v{nomor}_{nama_file}.xlsx`.
* [ ] Integrasi skrip Python `parsers/generic_cleaner.py`:
  * Deteksi dan pembersihan otomatis sel yang dimerge (*unmerge cells*).
  * Pembersihan baris kosong dan normalisasi tanda desimal (koma ke titik).
* [ ] Pembuatan panel riwayat versi berkas mentah OPD beserta fitur *Rollback Data*.

#### Pekan 6: Fuzzy Matching Nama Wilayah & Agregator Data Individu
* [ ] Pembuatan `FuzzyMatchService.php` berbasis algoritma Levenshtein distance:
  * Deteksi otomatis salah ketik nama kecamatan dan desa dari dinas (ambang batas kemiripan $\ge 85\%$)[cite: 1, 2].
  * Kamus koreksi otomatis terhadap 31 kecamatan dan 248 desa/kelurahan di Jember[cite: 2].
* [ ] Integrasi skrip Python `parsers/individual_aggregator.py`:
  * Membaca berkas data individu per institusi (contoh: data Dapodik/EMIS per sekolah)[cite: 1, 2].
  * Eksekusi agregasi otomatis (*Group By*) level desa untuk KDA dan level kecamatan untuk DDA secara simultan[cite: 1, 2].
* [ ] Penyimpanan hasil matriks tabel bersih ke tabel database `publication_tables`.

#### Pekan 7: Modul Analitik Khusus Survei Kebutuhan Data (SKD)
* [ ] Integrasi skrip Python `calculators/skd_engine.py`:
  * Ekstraksi otomatis data mentah kuesioner VKD (Blok I, II, dan III)[cite: 3].
  * Kalkulasi rata-rata kepuasan ($\bar{x}_i$), kepentingan ($\bar{y}_i$), dan penimbang berbobot ($w_i$)[cite: 3].
  * Kalkulasi Indeks Kepuasan Konsumen (IKK) dan Indeks Persepsi Anti Korupsi (IPAK) skala 100 sesuai Permenpan RB No. 14/2017[cite: 3].
  * Kalkulasi nilai Kesenjangan / Gap ($\bar{x} - \bar{y}$) dan Tingkat Kesesuaian ($TK = \frac{\bar{x}}{\bar{y}} \times 100\%$)[cite: 3].
* [ ] Otomasi Diagram Kartesius IPA:
  * Pengelompokan 12 atribut pelayanan ke Kuadran A, B, C, dan D[cite: 3].
  * Rendering grafik kuadran format vektor SVG tajam menggunakan pustaka Matplotlib[cite: 3].
* [ ] Pengisian otomatis matriks rekomendasi dan rencana tindak lanjut (Lampiran 14 & 15 SKD) ke dalam database[cite: 3].

#### Pekan 8: Engine Narasi Ulasan Dinamis & Editor Bilingual
* [ ] Pembangunan `NarrativeEngineService.php`:
  * Parsing dan substitusi token dinamis (`{{ token }}`) pada template ulasan bab berdasarkan data aktual di tabel[cite: 1, 2].
* [ ] Pembuatan antarmuka visual editor teks ulasan untuk peran Editor:
  * Tampilan editor dua kolom berdampingan (Bahasa Indonesia di panel kiri, Bahasa Inggris di panel kanan)[cite: 1, 2].
  * Fitur *Sync Tokens*: tombol untuk memperbarui angka indikator jika ada revisi tabel tanpa merusak modifikasi kalimat manual yang telah dibuat Editor.
* [ ] Penyimpanan teks ulasan terkurasi ke dalam tabel `chapter_narratives`.

---

### BULAN 3: DESEMBER 2026 — WORKFLOW LOCKING, BATCH QUEUE & SIMULASI DATA RIIL

#### Pekan 9: Workflow State Machine, Status Locking & Deadline Tracking
* [ ] Penerapan alur kontrol status:  
  `PENDING_DATA` $\rightarrow$ `DATA_INGESTED` $\rightarrow$ `IN_EDITORIAL` $\rightarrow$ `PENDING_APPROVAL` $\rightarrow$ `APPROVED_LOCKED` $\rightarrow$ `FINAL_RELEASED`.
* [ ] Penerapan mekanisme penguncian ketat (*Strict Locking*):
  * Bab berstatus `APPROVED_LOCKED` terkunci permanen dari perubahan data tabel maupun narasi ulasan.
  * Hanya Approver yang memiliki wewenang membuka kunci (*Unlock*) dengan kewajiban mengisi log alasan revisi.
* [ ] Penerapan `CheckDeadline` middleware:
  * *Soft Deadline*: Peringatan visual kuning di dashboard jika batas waktu tersisa $\le 3$ hari.
  * *Hard Deadline*: Formulir unggah dan editor terkunci otomatis saat melewati batas waktu.
  * Konfigurasi urutan rilis: DDA $\rightarrow$ SKD $\rightarrow$ KDA[cite: 1, 2, 3].

#### Pekan 10: Generator Cover Dinamis & Antrean Render Masal (31 KDA)
* [ ] Integrasi modul cover depan otomatis berbasis Typst (injeksi judul, tahun, nomor volume, nomor katalog, barcode ISSN vektor, dan logo BPS)[cite: 1, 2, 3].
* [ ] Implementasi fitur *Manual Override*:
  * Sakelar untuk mengganti cover otomatis atau grafik bawaan dengan berkas gambar/PDF kustom rancangan desainer grafis luar.
* [ ] Pembangunan antrean tugas kompilasi `CompilePublicationJob.php` menggunakan Laravel Queue:
  * Eksekusi fitur **"Kompilasi Seluruh 31 Kecamatan KDA"** di latar belakang (*background worker*)[cite: 1, 2].
  * Bilah progres (*progress bar*) realtime di dashboard web yang memantau status render tiap kecamatan.

#### Pekan 11: Uji Penerimaan Pengguna (UAT) dengan Data Riil 2026
* [ ] Uji coba sistem menggunakan data mentah riil publikasi tahun 2026:
  * Input berkas kompilasi kecamatan untuk merekonstruksi buku KDA (contoh: KDA Mumbulsari 2025/2026)[cite: 1].
  * Input berkas kompilasi dinas untuk buku DDA Jember 2026[cite: 2].
  * Input data mentah kuesioner VKD untuk publikasi Analisis SKD 2025/2026[cite: 3].
* [ ] Verifikasi komparatif antara PDF hasil render SI-PENA dengan buku terbitan versi InDesign lama:
  * Pemeriksaan presisi penomoran halaman Romawi dan Arabik[cite: 1, 2, 3].
  * Pemeriksaan keutuhan teks ulasan narasi bilingual[cite: 1, 2].
  * Pemeriksaan akurasi rumus IKK, IPAK, dan kuadran Kartesius SKD[cite: 3].

#### Pekan 12: Optimalisasi Kinerja Server, Perbaikan Bug, & Gladi Bersih
* [ ] Pengecekan stabilitas alokasi memori RAM Windows 11 saat merender buku tebal DDA ($>350$ halaman)[cite: 2].
* [ ] Optimalisasi query database MySQL 8 (indeks pada tabel `publication_tables` dan `raw_data_files`).
* [ ] Simulasi peran penuh (*role-playing rehearsal*):
  * Operator mengunggah dan memetakan data.
  * Editor menyunting ulasan bilingual.
  * Approver memvalidasi angka, mengunci bab, dan mengeksekusi rilis final.
  * Viewer memantau progres dan mengunduh draf ber-watermark.
* [ ] Penyusunan dokumen panduan operasional ringkas (*One-Page User Manual*) untuk staf kantor.

---

### BULAN 4: JANUARI 2027 — DEPLOYMENT PRODUKSI & OFFICIAL GO-LIVE

#### Pekan 13: Setup Layanan Produksi Intranet Windows 11
* [ ] Konfigurasi web server intranet pada alamat IP lokal statis kantor (port 8000).
* [ ] Pendaftaran `php artisan serve` dan `php artisan queue:work` sebagai Windows Service permanen menggunakan utilitas **NSSM (Non-Sucking Service Manager)** agar aplikasi otomatis aktif saat server dinyalakan.
* [ ] Konfigurasi skrip otomatisasi pencadangan database harian (`backup_db.bat`) pada Windows Task Scheduler setiap pukul 23.00 WIB.
* [ ] Uji coba akses simultan dari laptop pegawai melalui jaringan WiFi dan LAN kantor BPS Kabupaten Jember.

#### Pekan 14: Sosialisasi Internal & Bimbingan Teknis (Bimtek) Staf
* [ ] Pelaksanaan sesi pelatihan teknis bagi seluruh anggota Tim Pengolahan dan Diseminasi:
  * Sesi Operator: Tata cara unggah data rekap/individu, mapping kolom, dan penanganan typo wilayah.
  * Sesi Editor: Tata cara kurasi narasi ulasan bilingual dan pengelolaan token angka[cite: 1, 2].
  * Sesi Approver: Tata cara pemeriksaan konsistensi tabel, mekanisme locking status, dan kompilasi PDF final siap rilis[cite: 1, 2].
* [ ] Pembagian akun login resmi untuk seluruh pengguna sesuai perannya.

#### Pekan 15: OFFICIAL GO-LIVE SISTEM SI-PENA
* [ ] Peluncuran resmi sistem SI-PENA di lingkungan internal BPS Kabupaten Jember.
* [ ] Penetapan SI-PENA sebagai platform tunggal resmi penyusunan publikasi tahun 2027.
* [ ] Penutupan (*decommissioning*) alur kerja lama berbasis penataan layout manual Adobe InDesign untuk KDA, DDA, dan SKD.

#### Pekan 16: Kick-off Penyusunan DDA 2027
* [ ] Pembukaan proyek kerja pertama: **Kabupaten Jember Dalam Angka 2027**[cite: 2].
* [ ] Operator mulai mengunggah berkas Excel yang disetor oleh dinas/OPD/lembaga eksternal ke dalam sistem SI-PENA.
* [ ] Pengawalan intensif proses kompilasi untuk mengejar target rilis publikasi DDA pada bulan **Februari 2027**[cite: 2].

---

## 3. Matriks Manajemen Risiko & Mitigasi (Risk Management)

| Risiko Operasional / Teknis | Dampak | Probabilitas | Rencana Mitigasi Sistem |
| :--- | :---: | :---: | :--- |
| **Dinas OPD mengubah struktur kolom secara ekstrem** | Tinggi | Sedang | Sistem menyediakan fitur *Schema Mapping Manager* yang memungkinkan konfigurasi kolom disimpan ulang dalam format JSON tanpa merombak database. |
| **PC Server Windows 11 restart mendadak (Windows Update)** | Sedang | Tinggi | Seluruh antrean tugas dan web server didaftarkan sebagai Windows Service permanen via NSSM; servis otomatis berjalan saat sistem menyala kembali. |
| **File Excel OPD rusak (*corrupt*) atau terkena macro/virus** | Sedang | Rendah | Validasi MIME type ketat pada Controller Laravel dan pembacaan berkas dijalankan di dalam lingkungan terisolasi Python Pandas. |
| **Sengketa angka revisi antara BPS dan instansi kontributor** | Tinggi | Sedang | Modul audit trail menyimpan setiap versi berkas mentah asli yang disetor OPD lengkap dengan hash SHA-256 dan stempel waktu pengunggah. |
| **Typst gagal menemukan font di sistem Windows** | Sedang | Rendah | Parameter CLI Typst secara eksplisit menyertakan argumen `--font-path "C:\\Windows\\Fonts"` untuk memastikan font Roboto/Open Sans terbaca. |

---

## 4. Indikator Keberhasilan (KPI Go-Live Januari 2027)

1. **Pemangkasan Durasi Layout KDA:** Waktu penataan layout 31 kecamatan terpangkas dari hitungan minggu menjadi **di bawah 10 menit** melalui fitur batch compilation[cite: 1, 2].
2. **Nol Inkonsistensi Angka Narasi:** Terwujudnya konsistensi angka 100% antara teks ulasan narasi bab dengan angka pada tabel di bawahnya berkat *Token Replacement Engine*[cite: 1, 2].
3. **Penyusunan SKD Instan:** Perhitungan IKK, IPAK, matriks kesenjangan Gap, dan diagram kartesius IPA tersaji dalam hitungan detik setelah kuesioner VKD diunggah[cite: 3].
4. **Kepatuhan Rilis DDA 2027:** Publikasi Kabupaten Jember Dalam Angka 2027 berhasil terbit tepat waktu pada bulan Februari 2027 melalui sistem SI-PENA tanpa kendala layout[cite: 2].