#import "components/cover.typ": front-cover
#import "components/divider.typ": chapter-divider
#import "components/tables.typ": bps-table

#let kda-document(
  title: "Kecamatan Kencong Dalam Angka 2026",
  year: "2026",
  volume: "18",
  catalog_no: "1102001.3509010",
  pub_no: "35090.26010",
  issn: "2828-1234",
  district_name: "Kencong",
  body,
) = {
  // Page setup for A5 BPS standard
  set document(title: title, author: "BPS Kabupaten Jember")
  set text(font: ("Roboto", "Liberation Sans", "Arial"), size: 9pt, fill: rgb("#2C3E50"))
  set page(
    paper: "a5",
    margin: (inside: 2.2cm, outside: 1.8cm, top: 2.0cm, bottom: 2.0cm),
    header: [
      #text(size: 7.5pt, fill: rgb("#7F8C8D"))[
        #title #h(1fr) BPS Kabupaten Jember
      ]
      #v(-4pt)
      #line(length: 100%, stroke: 0.4pt + rgb("#CBD5E1"))
    ],
    footer: [
      #line(length: 100%, stroke: 0.4pt + rgb("#CBD5E1"))
      #v(-2pt)
      #context {
        let p = counter(page).display("1")
        align(right)[#text(size: 7.5pt, fill: rgb("#7F8C8D"))[#p]]
      }
    ],
  )

  // Cover Page
  front-cover(
    title: title,
    year: year,
    volume: volume,
    catalog_no: catalog_no,
    pub_no: pub_no,
    issn: issn,
  )

  // Kata Pengantar
  align(center)[
    #text(size: 14pt, weight: "bold", fill: rgb("#0A3866"))[KATA PENGANTAR / PREFACE]
  ]
  v(0.8cm)

  grid(
    columns: (1fr, 1fr),
    gutter: 18pt,
    [
      #text(weight: "bold", size: 9pt)[KATA PENGANTAR] \ \
      Publikasi *Kecamatan #district_name Dalam Angka #year* merupakan publikasi berkala tahunan yang diterbitkan oleh Badan Pusat Statistik (BPS) Kabupaten Jember. Publikasi ini menyajikan statistik dan informasi penting tentang potensi, perkembangan sosial, dan ekonomi di Kecamatan #district_name.

      Kepada seluruh pihak yang telah memberikan bantuan dan partisipasi hingga terbitnya publikasi ini, disampaikan terima kasih dan penghargaan yang setinggi-tingginya.
      
      #v(0.6cm)
      #align(right)[
        Jember, September #year \
        *Kepala BPS Kabupaten Jember*
      ]
    ],
    [
      #text(weight: "bold", style: "italic", size: 9pt, fill: rgb("#7F8C8D"))[PREFACE] \ \
      #text(style: "italic", fill: rgb("#475569"))[
        *#district_name Subdistrict in Figures #year* is an annual publication published by BPS-Statistics of Jember Regency. This publication presents vital statistics and information regarding the potential, social, and economic development in #district_name Subdistrict.

        To all parties who have contributed and supported the publication of this edition, we would like to express our highest gratitude and appreciation.
      ]
      
      #v(0.6cm)
      #align(right)[
        #text(style: "italic", fill: rgb("#475569"))[
          Jember, September #year \
          *Chief Statistician of Jember Regency*
        ]
      ]
    ]
  )

  pagebreak()

  body
}

// Bilingual Narrative Macro (Two Columns)
#let narrative-section(title_id, title_en, text_id, text_en) = [
  #v(6pt)
  #text(size: 11pt, weight: "bold", fill: rgb("#0A3866"))[ULASAN / DESCRIPTION]
  #v(4pt)
  #grid(
    columns: (1fr, 1fr),
    gutter: 14pt,
    [
      #text(weight: "bold", size: 8.5pt, fill: rgb("#0A3866"))[#upper(title_id)] \
      #v(2pt)
      #text(size: 8.5pt, fill: rgb("#1E293B"))[#text_id]
    ],
    [
      #text(weight: "bold", style: "italic", size: 8.5pt, fill: rgb("#64748B"))[#title_en] \
      #v(2pt)
      #text(style: "italic", size: 8.5pt, fill: rgb("#475569"))[#text_en]
    ]
  )
  #v(8pt)
]
