# SI-PENA (Sistem Penerbitan Angka)
**Satuan Kerja:** BPS Kabupaten Jember (Kode Wilayah 3509)  
**Tagline:** *"Goresan Angka Pasti untuk Masa Depan Jember."*  
**Akses Intranet/Lokal:** [http://sipena.test](http://sipena.test) atau `http://localhost:8000`

---

## 1. Ikhtisar Sistem
SI-PENA adalah sistem penerbitan dan penataan angka daerah berbasis web intranet untuk BPS Kabupaten Jember, dirancang dengan arsitektur **Tri-Language Separation**:
1. **PHP 8.2 (Laravel 11):** Web UI (Blade + Tailwind + Alpine.js), RBAC, manajemen publikasi, dan orkestrasi file.
2. **Python 3.10+ (CLI Engine):** Pembersihan data Excel OPD yang kotor (*merge cells*, baris kosong), agregasi data individu (Dapodik), kalkulasi matriks SKD (IKK, IPAK, Gap), dan plotting grafik SVG penduduk & iklim.
3. **Typst Standalone (`typst.exe` v0.15.1):** 100% *typesetting* dan perakitan PDF publikasi cetak standar BPS (KDA, DDA, SKD).

---

## 2. Cara Mengakses via `http://sipena.test`

Aplikasi telah dikonfigurasi pada Apache Laragon (Port 80 & 443 SSL):
- **Virtual Host:** `C:\laragon\etc\apache2\sites-enabled\auto.sipena.test.conf`
- **Document Root:** `C:\laragon\www\sipena\public`

### Langkah 1-Klik Mengaktifkan Domain:
Cukup jalankan berkas registrasi hosts:
1. Klik ganda berkas `register-sipena-domain.bat` yang terletak di:
   - `C:\laragon\www\register-sipena-domain.bat` atau
   - `C:\laragon\www\sipena\register-sipena-domain.bat`
2. Konfirmasi jendela izin Administrator (*User Account Control*).
3. Berkas akan otomatis menambahkan entri:
   ```text
   127.0.0.1      sipena.test  #laragon magic!
   ```
4. Browser akan otomatis terbuka ke [http://sipena.test](http://sipena.test).

*Catatan:* Anda juga bisa mengklik kanan ikon **Laragon** di system tray -> **Apache** -> **Reload**, maka Laragon akan otomatis menambahkan domain `sipena.test` ke file hosts.

---

## 3. Fitur Utama & Modul
- **Dashboard Eksekutif:** Monitoring 31 Kecamatan Dalam Angka (KDA), 1 Jember Dalam Angka (DDA), dan 1 Survei Kebutuhan Data (SKD).
- **Ingestion Data OPD:** Upload dan validasi otomatis berkas Excel dinas menggunakan Python worker.
- **Meja Redaksi & Editing:** Penyusunan narasi bab, tabel dinamis, dan verifikasi konsistensi data.
- **Kompilasi PDF Standar BPS (Typst):** Cetak publikasi sekelas InDesign/LaTeX dengan kecepatan milidetik.
- **Engine SKD & Analisis:** Analisis Kepuasan Konsumen (IKK, IPAK, Gap Analysis, Diagram Kartesius).
- **Desain Cover & Aset:** Personalisasi cover publikasi sesuai standar BPS.
