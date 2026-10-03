#import "components/cover.typ": front-cover
#import "components/divider.typ": chapter-divider
#import "components/tables.typ": bps-table
#import "components/narrative.typ": narrative-section
#import "components/state.typ": section-name, front-pages, body-started
#import "components/frontmatter.typ": (
  imprimatur,
  team-section,
  contributors-section,
  preface-id,
  toc-page,
  tables-index,
  explanatory-notes,
  abbreviations,
)

#let skd-document(
  title: "Analisis Hasil Survei Kebutuhan Data BPS Kabupaten Jember 2026",
  title_en: "Analysis of Data Needs Survey Results BPS Jember Regency 2026",
  year: "2026",
  volume: "9",
  catalog_no: "1305011.3509",
  pub_no: "35090.2602",
  issn: "2548-8120",
  book_size: "18,2 cm x 25,7 cm",
  contributors: (),
  team: (),
  has_tables: true,
  ikk_score: "90.96",
  ipak_score: "90.33",
  ikk_mutu: "",
  custom_cover_path: "",
  body,
) = {
  // Halaman sesuai contoh SKD: 18,2 cm x 25,7 cm.
  set document(title: title, author: "BPS Kabupaten Jember")
  set text(font: ("Roboto", "Arial"), size: 9.5pt, fill: rgb("#2C3E50"))
  set page(
    width: 18.2cm,
    height: 25.7cm,
    margin: (inside: 2.5cm, outside: 2.0cm, top: 2.2cm, bottom: 2.0cm),
    numbering: none,
    header: context [
      #text(size: 8pt, fill: rgb("#7F8C8D"))[
        #section-name.get() #h(1fr) #counter(page).display()
      ]
      #v(-1pt)
      #line(length: 100%, stroke: 0.5pt + rgb("#CBD5E1"))
    ],
    footer: [
      #line(length: 100%, stroke: 0.5pt + rgb("#CBD5E1"))
      #v(1pt)
      #text(size: 7pt, fill: rgb("#94A3B8"))[
        #title #h(1fr) https://jemberkab.bps.go.id
      ]
    ],
  )

  show heading.where(level: 1): set text(size: 14pt, weight: "bold", fill: rgb("#0A3866"))
  show heading.where(level: 2): set text(size: 11pt, weight: "bold", fill: rgb("#0A3866"))
  show heading: set block(above: 6pt, below: 5pt)
  show figure: set block(breakable: true)
  // Contoh SKD menaruh judul tabel di atas dan sumber di bawah.
  set figure.caption(position: top)

  front-cover(
    title: title,
    year: year,
    volume: volume,
    catalog_no: catalog_no,
    pub_no: pub_no,
    issn: issn,
    bg_image_path: custom_cover_path,
    next_section: "HALAMAN JUDUL",
  )

  set page(numbering: "i")

  imprimatur(
    title_id: title,
    title_en: title_en,
    year: year,
    volume: volume,
    catalog_no: catalog_no,
    pub_no: pub_no,
    issn: issn,
    book_size: book_size,
  )

  team-section(entries: team)
  contributors-section(names: contributors)

  preface-id(
    [
      Survei Kebutuhan Data (SKD) merupakan instrumen BPS untuk memperoleh masukan dari
      konsumen mengenai kebutuhan data serta persepsi terhadap kinerja pelayanan dan
      kualitas data yang dihasilkan. Hasil SKD dipakai untuk menetapkan Indeks Kepuasan
      Konsumen (IKK) dan Indeks Persepsi Anti Korupsi (IPAK) sebagai ukuran capaian
      pelayanan statistik di Kabupaten Jember.

      Publikasi *Analisis Hasil Survei Kebutuhan Data BPS Kabupaten Jember #year*
      menyajikan gambaran kebutuhan data konsumen, persepsi terhadap kinerja pelayanan
      Pusat Statistik Terpadu (PST) BPS, serta persepsi terhadap kualitas data BPS.
      Besarnya angka pada publikasi ini dihitung dari berkas kuesioner VKD yang telah
      diolah dan diverifikasi.

      Kepada seluruh konsumen dan unit kerja terkait yang telah meluangkan waktu
      mengisi kuesioner, disampaikan terima kasih.
    ],
    sign_date: "Jember, September " + year,
  )

  toc-page()
  if has_tables { tables-index() }
  explanatory-notes()
  abbreviations(terms: (
    ("BPS", "Badan Pusat Statistik", "Statistics Indonesia"),
    ("SKD", "Survei Kebutuhan Data", "Data Needs Survey"),
    ("VKD", "Kuesioner Survei Kebutuhan Data", "Data Needs Survey Questionnaire"),
    ("IKK", "Indeks Kepuasan Konsumen", "Consumer Satisfaction Index"),
    ("IPAK", "Indeks Persepsi Anti Korupsi", "Anti-Corruption Perception Index"),
    ("PST", "Pusat Statistik Terpadu", "Integrated Statistics Center"),
    ("IPA", "Importance Performance Analysis", "Importance Performance Analysis"),
    ("%", "persen", "percent"),
    ("rb", "ribu", "thousand"),
    ("jt", "juta", "million"),
  ))

  // Mulai isi: angka romawi -> arabik sejak halaman pertama isi.
  context { front-pages.update(counter(page).get().first()) }
  counter(page).update(0)
  body-started.update(true)
  section-name.update(title)
  pagebreak()
  set page(numbering: "1")

  // Ringkasan eksekutif dibuka pada halaman pertama isi.
  align(center)[
    #text(size: 16pt, weight: "bold", fill: rgb("#0A3866"))[RINGKASAN EKSEKUTIF / EXECUTIVE SUMMARY]
  ]
  v(0.8cm)

  grid(
    columns: (1fr, 1fr),
    gutter: 16pt,
    [
      #rect(
        width: 100%,
        fill: rgb("#EFF6FF"),
        stroke: 1pt + rgb("#BFDBFE"),
        radius: 6pt,
        inset: 12pt,
      )[
        #text(size: 9pt, weight: "bold", fill: rgb("#1E40AF"))[INDEKS KEPUASAN KONSUMEN (IKK)]
        #v(4pt)
        #text(size: 24pt, weight: "black", fill: rgb("#1D4ED8"))[#ikk_score]
        #v(2pt)
        #text(size: 8.5pt, fill: rgb("#1E3A8A"))[
          #if ikk_mutu != "" [Predikat: #ikk_mutu (Skala 100)] else [Skala 100]
        ]
      ]
    ],
    [
      #rect(
        width: 100%,
        fill: rgb("#ECFDF5"),
        stroke: 1pt + rgb("#A7F3D0"),
        radius: 6pt,
        inset: 12pt,
      )[
        #text(size: 9pt, weight: "bold", fill: rgb("#065F46"))[INDEKS PERSEPSI ANTI KORUPSI (IPAK)]
        #v(4pt)
        #text(size: 24pt, weight: "black", fill: rgb("#047857"))[#ipak_score]
        #v(2pt)
        #text(size: 8.5pt, fill: rgb("#064E3B"))[Skala 100]
      ]
    ]
  )

  v(1cm)
  body
}
