// Status lintas komponen untuk halaman cetak.
// section-name  : judul bagian aktif, ditampilkan pada header setiap halaman.
// front-pages   : jumlah halaman front matter (romawi) untuk kolofon.
// body-started  : penomoran arabik sudah dimulai (ada halaman isi).
#let section-name = state("section-name", "")
#let front-pages = state("front-pages", 0)
#let body-started = state("body-started", false)
