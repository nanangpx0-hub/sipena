# MODUL 05: INTEGRASI WORKER PYTHON & ENGINE TYPST
## SI-PENA (Sistem Penerbitan Angka) - BPS Kabupaten Jember

Dokumen ini menjelaskan implementasi teknis dan interaksi skrip Python CLI dan mesin kompilasi Typst pada SI-PENA.

---

## 1. Engine Pemrosesan Data Python (`python_engine/`)

### 1.1 `parsers/generic_cleaner.py`
* **Tujuan:** Membersihkan lembar kerja Excel OPD yang memiliki struktur acak (*unstructured*).
* **Fitur Utama:**
  - Mendeteksi baris header secara dinamis.
  - Membuka sel yang digabung (*unmerge cells*) dan mengisi nilai referensinya (*forward fill*).
  - Menghapus baris/kolom kosong tanpa data.
  - Mengonversi format angka, tanggal, dan desimal campuran ke standar numerik.
  - Melakukan fuzzy matching terhadap nama desa di Jember dengan skor threshold minimum 80%.

### 1.2 `parsers/individual_aggregator.py`
* **Tujuan:** Mengagregasi data mikro berbasis baris (contoh: daftar nama siswa per sekolah, atau data individu petani).
* **Output:** Tabel rekapitulasi jumlah menurut tingkatan wilayah (desa atau kecamatan).

### 1.3 `calculators/skd_engine.py`
* **Tujuan:** Menghitung seluruh indikator teknis Survei Kebutuhan Data (SKD).
* **Rumus Perhitungan:**
  - **Indeks Kepuasan Konsumen (IKK):**
    $$IKK = \frac{\sum (Bobot \times Rata\_Kepuasan)}{Total\_Bobot} \times 25$$
  - **Indeks Persepsi Anti Korupsi (IPAK):**
    $$IPAK = \frac{\sum (Bobot \times Rata\_Persepsi)}{Total\_Bobot} \times 25$$
  - **Gap Analysis:**
    $$Gap_i = Kepuasan_i - Kepentingan_i$$
  - **Diagram Kartesius (IPA):**
    Menentukan sumbu potong berdasarkan rata-rata total kepuasan ($\bar{X}$) dan rata-rata total kepentingan ($\bar{Y}$) untuk memetakan tiap atribut ke Kuadran A, B, C, atau D.

### 1.4 `visualizers/population_pyramid.py` & `climate_chart.py`
* **Tujuan:** Menghasilkan grafik vektor SVG dengan Matplotlib.
* **Format:** File `.svg` murni yang disematkan langsung ke dalam halaman Typst tanpa kehilangan ketajaman saat dicetak (*lossless vector graphic*).

---

## 2. Engine Typesetting Typst (`typst_engine/`)

### 2.1 Mengapa Typst?
Typst dipilih sebagai mesin utama menggantikan InDesign dan LaTeX karena:
* **Performa Ekstrem:** Waktu kompilasi buku 80 halaman kurang dari 0.5 detik.
* **Tata Letak Presisi:** Kontrol matematis atas margin, header, footer, penomoran halaman Romawi/Arabik, dan penataan kolom.
* **Programmatic Markup:** Mendukung penulisan logika kondisional, perulangan tabel data, dan impor modul komponen secara elegan.

### 2.2 Template Master KDA (`templates/kda_master.typ`)
* **Ukuran Kertas:** A5 (148mm x 210mm).
* **Margin Cetak:** Dalam 20mm (gutter jilid), Luar 15mm, Atas 20mm, Bawah 20mm.
* **Tipografi:** Noto Sans / Arial untuk keterbacaan tinggi angka statistik.
* **Struktur Halaman:**
  1. Halaman Judul Dalam & Hak Cipta (*Cataloging-in-Publication*).
  2. Kata Pengantar Kepala BPS Kabupaten Jember.
  3. Daftar Isi, Daftar Tabel, dan Daftar Gambar Otomatis.
  4. Halaman Pembatas Bab Resmi (*Chapter Dividers*) berlatar biru BPS.
  5. Narasi Ulasan Bilingual (Kolom ganda atau format paralel ID/EN).
  6. Tabel Data Statistik dengan garis pembatas standar publikasi BPS.

### 2.3 Komponen Modular
* `templates/components/cover.typ`: Tata letak sampul depan dan belakang lengkap dengan logo BPS, judul publikasi, tahun edisi, dan barcode ISBN.
* `templates/components/divider.typ`: Halaman pemisah antar-bab dengan nomor bab besar dan ringkasan angka kunci (*highlight numbers*).
* `templates/components/tables.typ`: Standar format tabel statistik BPS dengan baris garis tebal atas/bawah dan striping baris yang bersih.

### 2.4 Perintah Kompilasi CLI
```powershell
& "C:\laragon\www\sipena\typst_engine\bin\typst.exe" compile `
  "C:\laragon\www\sipena\storage\temp\temp_pub_1.typ" `
  "C:\laragon\www\sipena\storage\output_pdf\2026\kecamatan-kencong-dalam-angka-2026.pdf" `
  --root "C:\laragon\www\sipena"
```
