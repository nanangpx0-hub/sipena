# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## SI-PENA (Sistem Penerbitan Angka)
**Target Pengguna:** BPS Kabupaten Jember  
**Platform:** Intranet Web Local (Host: Windows 11 Pro)  
**Teknologi:** PHP 8.2 (Laravel), MySQL 8.0, Python 3.11+, Typst CLI  

---

## 1. Executive Summary & Visi Produk
SI-PENA (Sistem Penerbitan Angka) adalah platform web intranet yang dirancang untuk mengeliminasi alur kerja manual berbasis Adobe InDesign dalam penyusunan 3 jenis publikasi tahunan di BPS Kabupaten Jember:
1. **Kecamatan Dalam Angka (KDA):** 31 buku publikasi kecamatan, format A5 bilingual (ID/EN), 7 bab per buku.
2. **Kabupaten Jember Dalam Angka (DDA):** Buku induk statistik daerah, format A5/B5 bilingual, 350+ halaman, 13 bab kompilasi lintas OPD.
3. **Analisis Hasil Survei Kebutuhan Data (SKD):** Laporan analitis tahunan, format B5, berbasis kuesioner VKD dengan kalkulasi matriks IKK, IPAK, Gap Analysis, dan Diagram Kartesius Importance and Performance Analysis (IPA).

Sistem menggabungkan basis data relasional MySQL 8, modul ekstraksi Python, antarmuka kolaboratif PHP 8.2, dan mesin programmatic typesetting modern (Typst) untuk menghasilkan PDF siap cetak/rilis secara otomatis.

---

## 2. Masalah Utama & Solusi Fungsional

| Masalah Eksisting | Dampak Operasional | Solusi SI-PENA |
| :--- | :--- | :--- |
| **Inkonsistensi Excel OPD** | Baris kosong, merged cells, format tanggal/desimal bercampur, dan typo nama desa/wilayah. | **Dual-Mode Ingestion Engine:** Parser otomatis berbasis Python (Pandas) dengan Fuzzy Matching terhadap 31 kecamatan dan 248 desa di Jember. |
| **Beban Desain Berulang** | Membuat manual 217 cover pembatas bab KDA (31 kec x 7 bab) dan puluhan pembatas DDA di InDesign. | **Dynamic Typst Generator:** Cover dan divider beraksen resmi digenerate otomatis dengan angka highlight yang diambil langsung dari database. |
| **Desinkronisasi Ulasan** | Angka pada narasi bab terlambat diubah saat tabel data diperbaiki menjelang deadline. | **Token-Based Auto-Narrative:** Pola kalimat ulasan terisi otomatis via token data, dengan antarmuka editor bilingual untuk penyesuaian manual. |
| **Rumus & Kuadran SKD Manual** | Menghitung bobot IKK, IPAK, Gap, dan menggambar kuadran Cartesius secara manual di Excel. | **Automated SKD Engine:** Unggah mentah VKD langsung menghasilkan angka indeks resmi dan grafik vektor kuadran IPA (A, B, C, D). |
| **Sengketa Revisi Angka OPD** | Tidak ada bukti berkas versi awal saat dinas merevisi data berulang kali. | **Immutable Raw Versioning:** Penyimpanan file mentah OPD berbasis SHA-256 dan riwayat versi yang tidak bisa ditimpa (audit trail). |

---

## 3. Matriks Peran Pengguna (RBAC)

Sistem membagi wewenang ke dalam 4 peran:
* **Operator (Data Specialist):** Mengunggah file Excel mentah OPD, mencocokkan mapping kolom, dan memverifikasi data tabel.
* **Editor (Editorial Specialist):** Menyunting teks ulasan bab bilingual (ID/EN), menyesuaikan redaksi kalimat ulasan, dan mengunggah gambar/peta kustom.
* **Approver (Ketua Tim / Koordinator):** Memvalidasi konsistensi tabel, mengunci status bab (locking), menyetujui draf, dan mengeksekusi kompilasi PDF final siap rilis.
* **Viewer (Pimpinan / Seksi Terkait):** Akses baca (read-only), memantau progres deadline 31 kecamatan, dan mengunduh draf ber-watermark.

---

## 4. Siklus Status Bab (Workflow State Machine)

Setiap bab dalam publikasi harus melewati alur kontrol kualitas:

```text
[1. PENDING_DATA] ──► (Operator Upload Excel)
         │
         ▼
[2. DATA_INGESTED] ──► (Sistem Agregasi & Validasi Tabel)
         │
         ▼
[3. IN_EDITORIAL] ──► (Editor Memeriksa & Menyesuaikan Narasi)
         │
         ▼
[4. PENDING_APPROVAL] ──► (Pengajuan ke Ketua Tim)
         │
         ├─► [REVISION_REQUIRED] ──► (Ditolak kembali ke Operator/Editor)
         │
         ▼
[5. APPROVED_LOCKED] ──► (Terkunci permanen, tidak bisa diedit)
         │
         ▼
[6. FINAL_RELEASED] ──► (Dikompilasi menjadi PDF siap rilis)