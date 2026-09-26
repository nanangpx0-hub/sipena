# DESIGN_SYSTEM.md
## Sistem Desain & Standar Visual SI-PENA (Sistem Penerbitan Angka)
**Penyusun:** BPS Kabupaten Jember  
**Cakupan:** Format Dokumen Cetak (Typst Engine) & Antarmuka Aplikasi Web (Tailwind CSS)  
**Target Publikasi:** Kecamatan Dalam Angka (KDA), Kabupaten Jember Dalam Angka (DDA), dan Analisis SKD  

---

## 1. Filosofi & Karakter Desain
Desain publikasi dan antarmuka sistem SI-PENA (Sistem Penerbitan Angka) mengusung prinsip **"Fungsional, Presisi, Terstruktur, dan Berstandar Nasional"**.
1. **Data-Ink Ratio Maksimal:** Meminimalkan elemen ornamen dekoratif yang tidak perlu; fokus pada kejelasan angka statistik dan kenyamanan pembacaan.
2. **Kepatuhan Pedoman BPS RI:** Mengikuti aturan resmi tata letak publikasi Badan Pusat Statistik (tabel terbuka tanpa garis vertikal, penomoran kolom eksplisit, dan struktur bilingual berdampingan).
3. **Pemisahan Tegas Dua Moda:** Membedakan token visual untuk **media cetak/PDF (CMYK/vektor resolusi tinggi)** dan **media layar/web (RGB responsif)**.

---

## 2. Token Warna (Color Tokens)

### A. Palet Cetak & Publikasi (Typst Document Tokens)
Warna baku yang diterapkan pada cover, pembatas bab, tabel, dan grafik publikasi:

| Token Name | Hex Code | CMYK Equivalent | Penggunaan Utama |
| :--- | :---: | :---: | :--- |
| `bps-primary-navy` | `#0A3866` | C:100 M:75 Y:20 K:30 | Warna utama korporat BPS, border kepala tabel, judul bab DDA. |
| `bps-accent-orange` | `#E67E22` | C:0 M:60 Y:100 K:0 | Aksen nomor bab pembatas KDA/DDA, sorotan indikator kunci. |
| `bps-accent-dark-orange`| `#D35400`| C:10 M:80 Y:100 K:5 | Angka metrik kunci (*highlight statistics*) di lembar pembatas. |
| `bps-secondary-blue` | `#2980B9` | C:80 M:40 Y:0 K:0 | Grafik batang utama, garis tren, hyperlink dokumen digital. |
| `text-primary-id` | `#2C3E50` | C:0 M:0 Y:0 K:90 | Teks utama Bahasa Indonesia, angka isi sel tabel. |
| `text-secondary-en` | `#7F8C8D` | C:0 M:0 Y:0 K:60 | Teks terjemahan Bahasa Inggris, catatan kaki, nomor katalog. |
| `table-rule-dark` | `#2C3E50` | C:0 M:0 Y:0 K:90 | Garis horizontal tebal pembatas atas dan penutup tabel. |
| `table-rule-light` | `#BDC3C7` | C:0 M:0 Y:0 K:30 | Garis horizontal tipis pembagi sub-header kolom. |
| `surface-soft-gray` | `#F8F9FA` | C:0 M:0 Y:0 K:3 | Kotak latar ulasan teknis, latar selang-seling baris tabel (opsional). |

### B. Palet Antarmuka Web (UI Application Tokens)
Warna untuk dashboard, formulir pemetaan data, dan badge status alur kerja:

| Token Name | Hex Code | Keterangan & Penggunaan |
| :--- | :---: | :--- |
| `ui-bg-slate` | `#F1F5F9` | Latar belakang kanvas aplikasi web (Slate-100). |
| `ui-card-white` | `#FFFFFF` | Latar panel kartu, form input, dan modal dialog. |
| `ui-border-light`| `#E2E8F0` | Garis pembatas komponen form dan tabel web. |
| `status-pending` | `#854D0E` / `#FEF9C3` | Teks / Latar badge: `PENDING_DATA` (Kuning). |
| `status-ingested`| `#1E40AF` / `#DBEAFE` | Teks / Latar badge: `DATA_INGESTED` (Biru Muda). |
| `status-editorial`| `#6B21A8` / `#F3E8FF` | Teks / Latar badge: `IN_EDITORIAL` (Ungu). |
| `status-approved` | `#065F46` / `#D1FAE5` | Teks / Latar badge: `APPROVED_LOCKED` (Hijau Tua). |
| `status-released` | `#15803D` / `#DCFCE7` | Teks / Latar badge: `FINAL_RELEASED` (Hijau Sukses). |
| `status-rejected` | `#991B1B` / `#FEE2E2` | Teks / Latar badge: `REVISION_REQUIRED` (Merah Galat). |

---

## 3. Sistem Tipografi (Typography)

### A. Hirarki Font Publikasi (Typst Typesetting)
Publikasi menggunakan keluarga font standar **`Roboto`** atau **`Open Sans`** (didukung font serif alternatif seperti `Libertinus Serif` jika diperlukan):

| Elemen Halaman | Berat (Weight) | Ukuran (Size) | Penyelarasan | Leading / Spacing |
| :--- | :---: | :---: | :---: | :---: |
| **Judul Buku Cover** | Black / Bold | 24 – 28 pt | Rata Kiri/Tengah | 1.15 em, ALL-CAPS |
| **Nomor Bab Pembatas** | Black | 54 – 64 pt | Rata Kiri/Atas | Solid, Warna Oranye |
| **Judul Bab (ID)** | Bold | 18 – 20 pt | Rata Kiri | 1.20 em, ALL-CAPS |
| **Judul Bab (EN)** | Italic Regular | 13 – 14 pt | Rata Kiri | Muted Gray, Title Case |
| **Judul Tabel (ID)** | Bold | 9 pt | Rata Kiri | 1.15 em |
| **Judul Tabel (EN)** | Italic Regular | 8 pt | Rata Kiri | Muted Gray |
| **Kepala Kolom Tabel** | Bold | 8 pt | Rata Tengah/Kiri | 1.10 em |
| **Nomor Kolom (1)(2)** | Regular | 7.5 pt | Rata Tengah | Format kurung siku/biasa |
| **Angka Data Tabel** | Regular (Tabular) | 8 pt | Rata Kanan | Angka Monospace/Tabular |
| **Teks Ulasan (Paragraf)** | Regular | 9.5 pt | Rata Kiri/Justify | 1.25 em (15 pt leading) |
| **Sumber / Catatan Kaki** | Regular | 7 pt | Rata Kiri | Singkat, tanpa justify |

### B. Aturan Terjemahan Bilingual (Bilingual Rules)
1. **Struktur Sejajar Vertikal (Judul & Header):**
   * Teks Bahasa Indonesia diletakkan di baris pertama (format tegak/bold).
   * Teks Bahasa Inggris diletakkan tepat di baris berikutnya (format miring/italic dengan warna abu-abu netral).
2. **Struktur Berdampingan Horizontal (Ulasan Narasi Bab):**
   * Narasi ulasan bab disajikan dalam format **Dua Kolom (*Two Columns*)**:
     * Kolom Kiri: Bahasa Indonesia (Heading: `ULASAN`).
     * Kolom Kanan: Bahasa Inggris (Heading: `DESCRIPTION`).
   * Kedua kolom dipisahkan oleh garis pemisah tipis (*vertical rule*) $0,5\text{ pt}$ opsional atau celah (*column gutter*) selebar $0,6\text{ cm}$.

---

## 4. Format Dokumen & Grid Halaman

### A. Dimensi Kertas & Margin Potong (Print Trim Box)

```text
       ┌───────────────────────────────┐
       │   Atas: 2,0 cm                │
       │  ┌─────────────────────────┐  │
       │  │                         │  │
Luar:  │  │  RUANG KONTEN           │  │  Dalam (Gutter):
1,8 cm │  │  (Tabel / Narasi)       │  │  2,2 cm (KDA)
       │  │                         │  │  2,5 cm (DDA)
       │  └─────────────────────────┘  │
       │   Bawah: 2,0 cm               │
       └───────────────────────────────┘