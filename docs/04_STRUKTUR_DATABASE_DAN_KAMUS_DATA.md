# MODUL 04: STRUKTUR BASIS DATA & KAMUS DATA
## SI-PENA (Sistem Penerbitan Angka) - BPS Kabupaten Jember

Dokumen ini memuat skema teknis relasional tabel basis data MySQL 8.0 pada aplikasi SI-PENA.

---

## 1. Skema Tabel Master Wilayah

### 1.1 Tabel `districts` (Kecamatan se-Kabupaten Jember)
Menyimpan referensi 31 wilayah kecamatan di bawah BPS Kabupaten Jember (Kode 3509).

| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED (PK) | Identifier unik kecamatan. |
| `code` | VARCHAR(10) (UNIQUE) | Kode resmi BPS (misal: `3509010` untuk Kencong). |
| `name` | VARCHAR(100) | Nama resmi kecamatan. |
| `capital_name` | VARCHAR(100) (NULL) | Nama ibukota kecamatan. |
| `total_villages` | INT (DEFAULT 0) | Jumlah desa/kelurahan dalam kecamatan. |
| `total_area_km2` | DECIMAL(8,2) (NULL) | Luas wilayah dalam kilometer persegi. |
| `created_at` | TIMESTAMP | Waktu pembuatan data. |
| `updated_at` | TIMESTAMP | Waktu pembaruan data terakhir. |

### 1.2 Tabel `villages` (Desa / Kelurahan)
Menyimpan 248 desa dan kelurahan resmi untuk keperluan fuzzy matching data OPD.

| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED (PK) | Identifier unik desa/kelurahan. |
| `district_id` | BIGINT UNSIGNED (FK) | Relasi ke `districts.id` (Cascade on delete). |
| `code` | VARCHAR(15) (UNIQUE) | Kode resmi BPS 10 digit. |
| `name` | VARCHAR(100) | Nama resmi desa/kelurahan. |
| `is_kelurahan` | BOOLEAN (DEFAULT 0) | 1 = Kelurahan, 0 = Desa. |

---

## 2. Skema Tabel Publikasi & Data Transaksional

### 2.1 Tabel `publications` (Buku Publikasi)
| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED (PK) | Identifier publikasi. |
| `type` | ENUM('KDA', 'DDA', 'SKD') | Jenis buku publikasi BPS. |
| `title` | VARCHAR(255) | Judul resmi publikasi. |
| `edition_year` | YEAR | Tahun edisi penerbitan (misal: 2026). |
| `district_id` | BIGINT UNSIGNED (FK/NULL) | Khusus KDA (berelasi ke `districts.id`). |
| `current_status` | ENUM | `DRAFT`, `IN_REVIEW`, `APPROVED`, `RELEASED`. |
| `final_pdf_path`| VARCHAR(255) (NULL) | Path PDF akhir di storage server. |
| `deadline_at` | DATETIME (NULL) | Batas tenggat waktu penyelesaian publikasi. |

### 2.2 Tabel `raw_data_files` (Arsip Berkas Mentah OPD)
| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED (PK) | Identifier file unggahan. |
| `publication_id`| BIGINT UNSIGNED (FK) | Relasi ke `publications.id`. |
| `chapter_number`| INT | Nomor bab tujuan (1 - 7). |
| `original_filename`| VARCHAR(255) | Nama file asli dari komputer pengunggah. |
| `stored_path` | VARCHAR(255) | Path penyimpanan file di server. |
| `file_sha256` | VARCHAR(64) | Nilai hash SHA-256 berkas untuk audit trail. |
| `uploaded_by` | BIGINT UNSIGNED (FK) | Relasi ke `users.id` (Operator pengunggah). |
| `parsed_status` | ENUM | `PENDING`, `PARSED_SUCCESS`, `PARSE_FAILED`. |

### 2.3 Tabel `publication_tables` (Tabel Terstruktur Publikasi)
| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED (PK) | Identifier tabel. |
| `publication_id`| BIGINT UNSIGNED (FK) | Relasi ke `publications.id`. |
| `chapter_number`| INT | Nomor bab letak tabel. |
| `table_number` | VARCHAR(20) | Penomoran resmi tabel (misal: `1.1.1`). |
| `title_id` | VARCHAR(255) | Judul tabel dalam Bahasa Indonesia. |
| `title_en` | VARCHAR(255) (NULL) | Judul tabel dalam Bahasa Inggris. |
| `table_data` | JSON | Struktur matriks tabel (kolom, baris, nilai data). |
| `unit` | VARCHAR(50) (NULL) | Satuan data tabel. |
| `source_note` | VARCHAR(255) (NULL) | Catatan sumber data resmi BPS/OPD. |

### 2.4 Tabel `chapter_narratives` (Ulasan Teks Bab Bilingual)
| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED (PK) | Identifier narasi bab. |
| `publication_id`| BIGINT UNSIGNED (FK) | Relasi ke `publications.id`. |
| `chapter_number`| INT | Nomor bab (1 s.d. 7). |
| `title` | VARCHAR(255) | Judul bab. |
| `content_id` | TEXT (NULL) | Teks narasi Bahasa Indonesia dengan token. |
| `content_en` | TEXT (NULL) | Teks narasi Bahasa Inggris dengan token. |
| `highlight_tokens`| JSON (NULL) | Key-value token data dinamis (`{token}`). |
| `status` | ENUM | Status bab (`PENDING_DATA` s.d. `APPROVED_LOCKED`). |

### 2.5 Tabel `workflow_logs` (Jejak Rekam / Audit Trail)
| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED (PK) | Identifier catatan audit. |
| `publication_id`| BIGINT UNSIGNED (FK) | Relasi ke `publications.id`. |
| `chapter_number`| INT (NULL) | Nomor bab terkait aksi. |
| `user_id` | BIGINT UNSIGNED (FK) | Pengguna yang melakukan tindakan. |
| `action` | VARCHAR(50) | Aksi sistem atau pengguna. |
| `old_status` | VARCHAR(50) (NULL) | Status sebelum aksi. |
| `new_status` | VARCHAR(50) (NULL) | Status setelah aksi. |
| `notes` | TEXT (NULL) | Alasan revisi, catatan, atau pesan audit. |
