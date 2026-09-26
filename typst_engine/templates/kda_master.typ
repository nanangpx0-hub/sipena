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
  preface-en,
  toc-page,
  tables-index,
  explanatory-notes,
  abbreviations,
)

#let kda-document(
  title: "Kecamatan Kencong Dalam Angka 2026",
  title_en: "Kencong District in Figures 2026",
  year: "2026",
  volume: "18",
  catalog_no: "1102001.3509010",
  pub_no: "35090.26010",
  issn: "2828-1234",
  district_name: "Kencong",
  book_size: "14,8 cm x 21 cm",
  contributors: (),
  team: (),
  has_tables: true,
  has_body: true,
  body,
) = {
  // Halaman sesuai contoh resmi KCA: A5 (14,8 cm x 21 cm).
  set document(title: title, author: "BPS Kabupaten Jember")
  set text(font: ("Roboto", "Arial"), size: 9pt, fill: rgb("#2C3E50"))
  set page(
    paper: "a5",
    margin: (inside: 2.2cm, outside: 1.8cm, top: 2.0cm, bottom: 1.8cm),
    numbering: none,
    header: context [
      #text(size: 7pt, fill: rgb("#7F8C8D"))[
        #section-name.get() #h(1fr) #counter(page).display()
      ]
      #v(-1pt)
      #line(length: 100%, stroke: 0.5pt + rgb("#CBD5E1"))
    ],
    footer: [
      #line(length: 100%, stroke: 0.5pt + rgb("#CBD5E1"))
      #v(1pt)
      #text(size: 6.5pt, fill: rgb("#94A3B8"))[
        #title #h(1fr) https://jemberkab.bps.go.id
      ]
    ],
  )

  show heading.where(level: 1): set text(size: 14pt, weight: "bold", fill: rgb("#0A3866"))
  show heading.where(level: 2): set text(size: 11pt, weight: "bold", fill: rgb("#0A3866"))
  show heading: set block(above: 6pt, below: 5pt)
  show figure: set block(breakable: true)

  front-cover(
    title: title,
    year: year,
    volume: volume,
    catalog_no: catalog_no,
    pub_no: pub_no,
    issn: issn,
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
      Publikasi *Kecamatan #district_name Dalam Angka #year* merupakan publikasi berkala
      tahunan yang diterbitkan oleh Badan Pusat Statistik (BPS) Kabupaten Jember. Publikasi
      ini menyajikan statistik dan informasi penting tentang potensi, perkembangan sosial,
      dan ekonomi di Kecamatan #district_name. Data pada publikasi ini merupakan hasil
      pengolahan data dari instansi pemerintah dan lembaga yang menjadi kontributor data.

      Kepada seluruh pihak yang telah memberikan bantuan dan partisipasi hingga terbitnya
      publikasi ini, disampaikan terima kasih dan penghargaan yang setinggi-tingginya.
    ],
    sign_date: "Jember, September " + year,
  )

  preface-en(
    [
      *#district_name Subdistrict in Figures #year* is an annual publication published by
      BPS-Statistics of Jember Regency. This publication presents vital statistics and
      information regarding the potential, social, and economic development in #district_name
      Subdistrict. The data are compiled from government agencies and institutions acting
      as data contributors.

      To all parties who have contributed and supported the publication of this edition, we
      would like to express our highest gratitude and appreciation.
    ],
    sign_date: "Jember, September " + year,
  )

  toc-page()
  if has_tables { tables-index() }
  explanatory-notes()
  abbreviations(terms: (
    ("BPS", "Badan Pusat Statistik", "Statistics Indonesia"),
    ("KDA", "Kecamatan Dalam Angka", "District in Figures"),
    ("DDA", "Kabupaten Dalam Angka", "Regency in Figures"),
    ("rb", "ribu", "thousand"),
    ("jt", "juta", "million"),
    ("%", "persen", "percent"),
    ("dpl", "di atas permukaan laut", "above sea level"),
    ("km", "kilometer", "kilometer"),
    ("ha", "hektar", "hectare"),
  ))

  // Mulai isi: angka romawi -> arabik sejak halaman pertama isi.
  context { front-pages.update(counter(page).get().first()) }
  if not has_body {
    // Tanpa isi (tanpa bab) -> tidak ada halaman kosong di akhir.
    none
  } else {
    counter(page).update(0)
    body-started.update(true)
    section-name.update(title)
    pagebreak()
    set page(numbering: "1")
    body
  }
}
