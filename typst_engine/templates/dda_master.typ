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

#let dda-document(
  title: "Kabupaten Jember Dalam Angka 2026",
  title_en: "Jember Regency in Figures 2026",
  year: "2026",
  volume: "54",
  catalog_no: "1102001.3509",
  pub_no: "35090.2601",
  issn: "0215-2231",
  book_size: "14,8 cm x 21 cm",
  contributors: (),
  team: (),
  has_tables: true,
  has_body: true,
  custom_cover_path: "",
  preface_id: "",
  preface_en: "",
  sign_date: "",
  custom_abbr: (),
  body,
) = {
  // Halaman sesuai contoh resmi: A5 (14,8 cm x 21 cm).
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

  // Cover (penomoran dimulai setelahnya dengan angka romawi).
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

  if preface_id != "" {
    preface-id(
      preface_id,
      sign_date: if sign_date != "" { sign_date } else { "Jember, September " + year },
    )
  } else {
    preface-id(
      [
        Publikasi *Kabupaten Jember Dalam Angka #year* merupakan publikasi komprehensif
        tahunan yang diterbitkan oleh Badan Pusat Statistik (BPS) Kabupaten Jember. Buku ini
        menyajikan data statistik sektoral dan data dasar yang mencakup kondisi geografi,
        sosial demografi, dan ekonomi di Kabupaten Jember. Data pada publikasi ini merupakan
        hasil pengolahan data dari instansi pemerintah dan lembaga yang menjadi kontributor
        data, sehingga diharapkan dapat digunakan sebagai referensi perencanaan dan evaluasi
        pembangunan di Kabupaten Jember.

        Kepada seluruh pihak yang telah memberikan bantuan dan partisipasi hingga terbitnya
        publikasi ini, disampaikan terima kasih dan penghargaan yang setinggi-tingginya.
      ],
      sign_date: if sign_date != "" { sign_date } else { "Jember, September " + year },
    )
  }

  if preface_en != "" {
    preface-en(
      preface_en,
      sign_date: if sign_date != "" { sign_date } else { "Jember, September " + year },
    )
  } else {
    preface-en(
      [
        *Jember Regency in Figures #year* is an annual comprehensive publication published by
        BPS-Statistics of Jember Regency. This book presents sectoral statistical data and
        fundamental indicators covering geography, social demography, and economics in Jember
        Regency. The data in this publication are compiled from government agencies and
        institutions acting as data contributors, and are expected to serve as a reference
        for development planning and evaluation in Jember Regency.

        Our highest gratitude goes to all parties who have contributed and supported the
        publication of this edition.
      ],
      sign_date: if sign_date != "" { sign_date } else { "Jember, September " + year },
    )
  }

  toc-page()
  if has_tables { tables-index() }
  explanatory-notes()

  let abbr_terms_list = if custom_abbr.len() > 0 { custom_abbr } else {
    (
      ("BPS", "Badan Pusat Statistik", "Statistics Indonesia"),
      ("DDA", "Kabupaten Dalam Angka", "Regency in Figures"),
      ("KDA", "Kecamatan Dalam Angka", "District in Figures"),
      ("rb", "ribu", "thousand"),
      ("jt", "juta", "million"),
      ("%", "persen", "percent"),
      ("dpl", "di atas permukaan laut", "above sea level"),
      ("km", "kilometer", "kilometer"),
      ("ha", "hektar", "hectare"),
    )
  }
  abbreviations(terms: abbr_terms_list)

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
