// Front matter resmi BPS: halaman judul (kolofon), tim penyusun,
// kontributor data, kata pengantar, daftar isi/tabel, penjelasan umum,
// dan daftar singkatan. Semua fungsi (kecuali imprimatur) wajib diawali
// section-name.update() + pagebreak() agar header halaman berikutnya benar.
#import "state.typ": section-name, front-pages, body-started

#let _kv(label, value) = block(
  below: 3.5pt,
  grid(
    columns: (6.3cm, 1fr),
    column-gutter: 6pt,
    text(size: 8.5pt, fill: rgb("#475569"))[#label:],
    text(size: 8.5pt, weight: "bold", fill: rgb("#0A3866"))[#value],
  ),
)

// Halaman judul tepat setelah cover (cover sudah menutup halamannya sendiri).
#let imprimatur(
  title_id: "",
  title_en: "",
  year: "",
  volume: "",
  catalog_no: "",
  pub_no: "",
  issn: "",
  book_size: "",
  illustrator: "",
) = [
  #v(1.1cm)
  #align(center)[
    #text(size: 14pt, weight: "black", fill: rgb("#0A3866"))[#upper(title_id)] \
    #v(5pt)
    #text(size: 10pt, style: "italic", fill: rgb("#475569"))[#title_en] \
    #v(8pt)
    #text(size: 11pt, weight: "bold", fill: rgb("#E67E22"))[#year]
  ]
  #v(0.8cm)
  #_kv("Katalog/Catalogue", catalog_no)
  #_kv("ISSN", issn)
  #if pub_no != "" [#_kv("Nomor Publikasi/Publication Number", pub_no)]
  #if volume != "" [#_kv("Volume", volume)]
  #_kv("Ukuran Buku/Book Size", book_size)
  #context[
    #let fp = front-pages.final()
    #let bp = if body-started.final() {
      counter(page).final().first()
    } else {
      0
    }
    #let pages = if bp > 0 {
      numbering("i", fp) + " + " + str(bp) + " hal/pages"
    } else {
      numbering("i", fp) + " hal/pages"
    }
    #_kv("Jumlah Halaman/Number of Pages", pages)
  ]
  #_kv("Penyusun Naskah/Manuscript Drafter", [BPS Kabupaten Jember / BPS-Statistics Jember Regency])
  #_kv("Penyunting/Editor", [BPS Kabupaten Jember / BPS-Statistics Jember Regency])
  #_kv("Pembuat Kover/Cover Designer", [BPS Kabupaten Jember / BPS-Statistics Jember Regency])
  #if illustrator != "" [#_kv("Sumber Ilustrasi/Illustration Source", illustrator)]
  #_kv("Penerbit/Publisher", [BPS Kabupaten Jember / BPS-Statistics Jember Regency])
  #v(0.5cm)
  #block(
    width: 100%,
    inset: 8pt,
    fill: rgb("#F8FAFC"),
    stroke: 0.5pt + rgb("#E2E8F0"),
  )[
    #text(size: 6.8pt, fill: rgb("#475569"))[
      Dilarang mereproduksi dan/atau menggandakan sebagian atau seluruh isi buku ini untuk
      tujuan komersial tanpa izin tertulis dari Badan Pusat Statistik Kabupaten Jember. \
      It is prohibited to reproduce and/or duplicate part or all of this book for commercial
      purpose without permission from BPS-Statistics Jember Regency.
    ]
  ]
]

// Tim penyusun diturunkan dari pengguna yang benar-benar terlibat pada
// publikasi (riwayat alur kerja + unggahan data mentah).
#let team-section(entries: ()) = {
  if entries.len() == 0 {
    none
  } else {
    section-name.update("TIM PENYUSUN")
    pagebreak()
    align(center)[
      #text(size: 14pt, weight: "bold", fill: rgb("#0A3866"))[TIM PENYUSUN / TEAM MEMBERS]
    ]
    v(0.9cm)
    for e in entries {
      block(width: 100%, below: 11pt)[
        #text(size: 10pt, weight: "bold", fill: rgb("#0A3866"))[
          #e.at("role_id") / #e.at("role_en")
        ]
        #v(4pt)
        #for n in e.at("names") [
          #text(size: 9pt, fill: rgb("#1E293B"))[#n] \
        ]
      ]
    }
  }
}

// Kontributor data: daftar OPD yang berkas mentahnya tercatat pada publikasi.
#let contributors-section(names: ()) = {
  if names.len() == 0 {
    none
  } else {
    section-name.update("KONTRIBUTOR DATA")
    pagebreak()
    align(center)[
      #text(size: 14pt, weight: "bold", fill: rgb("#0A3866"))[KONTRIBUTOR DATA / DATA CONTRIBUTOR]
    ]
    v(0.9cm)
    for (i, n) in names.enumerate() {
      block(below: 4pt)[#text(size: 9pt)[#{str(i + 1)}. #n]]
    }
  }
}

#let preface-id(text_id, sign_date: "") = [
  #section-name.update("KATA PENGANTAR")
  #pagebreak()
  #align(center)[#heading(level: 1)[KATA PENGANTAR]]
  #v(0.6cm)
  #set par(justify: true)
  #text(size: 9pt)[#text_id]
  #v(1cm)
  #align(right)[
    #text(size: 9pt)[#sign_date] \
    #v(2pt)
    #text(weight: "bold", size: 9pt)[Kepala BPS Kabupaten Jember]
  ]
]

#let preface-en(text_en, sign_date: "") = [
  #section-name.update("PREFACE")
  #pagebreak()
  #align(center)[#heading(level: 1)[PREFACE]]
  #v(0.6cm)
  #set par(justify: true)
  #text(size: 9pt, style: "italic", fill: rgb("#475569"))[#text_en]
  #v(1cm)
  #align(right)[
    #text(size: 9pt, style: "italic", fill: rgb("#475569"))[#sign_date] \
    #v(2pt)
    #text(weight: "bold", size: 9pt, style: "italic", fill: rgb("#475569"))[
      Chief Statistician of Jember Regency
    ]
  ]
]

#let toc-page() = [
  #section-name.update("DAFTAR ISI")
  #pagebreak()
  // Judul daftar isi tidak ikut masuk outline agar tidak mencatat dirinya.
  #heading(level: 1, outlined: false)[DAFTAR ISI/CONTENTS]
  #v(0.3cm)
  #outline(depth: 2, title: none)
]

// Daftar tabel hanya dipanggil bila publikasi memuat tabel.
#let tables-index() = [
  #section-name.update("DAFTAR TABEL")
  #pagebreak()
  #heading(level: 1)[DAFTAR TABEL/LIST OF TABLES]
  #v(0.3cm)
  #outline(target: figure.where(kind: table), depth: 2, title: none)
]

#let explanatory-notes() = [
  #section-name.update("PENJELASAN UMUM")
  #pagebreak()
  #heading(level: 1)[PENJELASAN UMUM/EXPLANATORY NOTES]
  #v(0.3cm)
  #grid(
    columns: (1fr, 1fr),
    gutter: 14pt,
    [
      #text(size: 8.5pt, fill: rgb("#1E293B"))[
        Publikasi ini menyajikan data statistik yang diperoleh dari instansi pemerintah,
        lembaga, dan sumber lain yang dicantumkan pada setiap tabel. Angka mengikuti satuan
        dan tahun rujukan yang tertulis pada judul tabel. Pemisah desimal menggunakan koma
        (,) sesuai kaidah penulisan angka di Indonesia. Tanda (-) berarti data tidak
        tersedia atau tidak berlaku, sedangkan 0 (nol) berarti data tersedia dengan nilai
        nol. Total merupakan jumlahan baris yang tercantum atau kelompok lain yang tidak
        dirinci. Setiap tabel memuat nomor kolom (1), (2), dan seterusnya beserta sumber
        data pada baris terakhir.
      ]
    ],
    [
      #text(size: 8.5pt, style: "italic", fill: rgb("#475569"))[
        This publication presents statistical data obtained from government agencies,
        institutions, and other sources listed in each table. Figures follow the units and
        reference years stated in the table titles. A comma (,) is used as the decimal
        separator. A dash (-) indicates data not available or not applicable, while 0 (zero)
        indicates available data with a zero value. Totals are sums of the listed rows or
        other undetailed groups. Each table includes column numbers (1), (2), and so on
        along with its data source.
      ]
    ],
  )
]

#let abbreviations(terms: ()) = [
  #section-name.update("DAFTAR SINGKATAN")
  #pagebreak()
  #heading(level: 1)[DAFTAR SINGKATAN/LIST OF ABBREVIATIONS]
  #v(0.3cm)
  #for t in terms {
    block(below: 5pt)[
      #text(weight: "bold", size: 8.5pt, fill: rgb("#0A3866"))[#t.at(0)]
      #h(6pt)
      #text(size: 8.5pt, fill: rgb("#1E293B"))[#t.at(1)]
      #h(6pt)
      #text(size: 8pt, style: "italic", fill: rgb("#7F8C8D"))[\/ #t.at(2)]
    ]
  }
]
