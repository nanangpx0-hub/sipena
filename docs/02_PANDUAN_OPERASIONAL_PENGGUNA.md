# MODUL 02: PANDUAN OPERASIONAL PENGGUNA
## SI-PENA (Sistem Penerbitan Angka) - BPS Kabupaten Jember

Panduan ini ditujukan bagi staf dan koordinator di lingkungan BPS Kabupaten Jember dalam menjalankan peran sehari-hari.

---

## 1. Peran Pengguna & Hak Akses

| Peran | Tugas Pokok | Halaman Utama |
| :--- | :--- | :--- |
| **Operator** | Mengunggah dan memverifikasi data Excel dari dinas/sektoral. | `/ingestion` |
| **Editor** | Menyusun dan menyesuaikan ulasan narasi bab (Bilingual). | `/editorial` |
| **Approver** | Memvalidasi kepatuhan tabel dan mengunci status bab (*Locking*). | `/approval`, `/compilation` |
| **Viewer** | Memantau grafik kemajuan publikasi dan mengunduh draf. | `/dashboard` |

---

## 2. Alur Pengunggahan Data Excel OPD (Operator)

1. Buka browser dan arahkan ke alamat: `http://sipena.test/ingestion`.
2. Pilih publikasi yang sedang dikerjakan (contoh: *Kecamatan Kencong Dalam Angka 2026*).
3. Tentukan nomor bab:
   * **Bab 1:** Geografi dan Iklim
   * **Bab 2:** Pemerintahan
   * **Bab 3:** Kependudukan
   * **Bab 4:** Sosial dan Kesejahteraan Rakyat
   * **Bab 5:** Pertanian, Kehutanan, Peternakan, dan Perikanan
   * **Bab 6:** Pariwisata, Transportasi, dan Komunikasi
   * **Bab 7:** Perbankan, Koperasi, dan Perdagangan
4. Klik **Pilih File** dan unggah berkas Excel asli dari OPD.
5. Klik **Unggah & Bersihkan Otomatis**.
6. Sistem akan mengekstraksi tabel, melakukan fuzzy matching desa, dan mengarahkan status bab ke tahap redaksi.

---

## 3. Alur Penyusunan Narasi Ulasan (Editor)

1. Masuk ke menu `http://sipena.test/editorial`.
2. Klik tombol **Edit Narasi** pada bab yang berstatus `DATA_INGESTED`.
3. Editor disajikan formulir bilingual:
   * **Kolom Kiri:** Narasi Bahasa Indonesia.
   * **Kolom Kanan:** Narasi Bahasa Inggris (*English Review*).
4. Gunakan token dinamis untuk menjaga konsistensi angka:
   * Contoh: `Pada tahun 2025, jumlah penduduk Kecamatan {nama_kecamatan} tercatat sebanyak {penduduk_total} jiwa.`
5. Klik **Simpan Narasi & Ajukan Persetujuan**.

---

## 4. Alur Persetujuan & Kompilasi (Approver)

1. Masuk ke menu `http://sipena.test/approval`.
2. Buka detail bab yang diajukan (`PENDING_APPROVAL`).
3. Periksa tabel, catatan kaki, sumber data, dan narasi ulasan.
4. **Tindakan:**
   * **Setujui (Approve):** Bab terkunci permanen (`APPROVED_LOCKED`).
   * **Tolak (Reject):** Masukkan alasan penolakan pada kotak catatan revisi, klik Tolak. Bab dikembalikan ke Editor dengan status `REVISION_REQUIRED`.
5. Setelah seluruh bab 1 s.d. 7 berstatus disetujui, buka menu `http://sipena.test/compilation`.
6. Klik **Kompilasi PDF Final**. Dalam beberapa detik berkas PDF siap unduh akan tersedia.

---

## 5. Alur Modul Survei Kebutuhan Data (SKD)

1. Buka menu `http://sipena.test/skd`.
2. Unggah data mentah kuesioner VKD (format Excel/CSV).
3. Sistem secara otomatis menghitung:
   * Indeks Kepuasan Konsumen (IKK)
   * Indeks Persepsi Anti Korupsi (IPAK)
   * Nilai Kesenjangan (*Gap*) tiap unsur pelayanan
   * Menghasilkan grafik kuadran Importance-Performance Analysis (IPA).
