#import "components/cover.typ": front-cover
#import "components/divider.typ": chapter-divider
#import "components/tables.typ": bps-table

#let dda-document(
  title: "Kabupaten Jember Dalam Angka 2026",
  year: "2026",
  volume: "54",
  catalog_no: "1102001.3509",
  pub_no: "35090.2601",
  issn: "0215-2231",
  body,
) = {
  // Page setup for B5 BPS standard (18.2cm x 25.7cm)
  set document(title: title, author: "BPS Kabupaten Jember")
  set text(font: ("Roboto", "Arial"), size: 9.5pt, fill: rgb("#2C3E50"))
  set page(
    paper: "iso-b5",
    margin: (inside: 2.5cm, outside: 2.0cm, top: 2.2cm, bottom: 2.2cm),
    header: [
      #text(size: 8pt, fill: rgb("#7F8C8D"))[
        #title #h(1fr) BPS Kabupaten Jember
      ]
      #v(-4pt)
      #line(length: 100%, stroke: 0.5pt + rgb("#CBD5E1"))
    ],
    footer: [
      #line(length: 100%, stroke: 0.5pt + rgb("#CBD5E1"))
      #v(-2pt)
      #context {
        let p = counter(page).display("1")
        align(right)[#text(size: 8pt, fill: rgb("#7F8C8D"))[#p]]
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

  align(center)[
    #text(size: 16pt, weight: "bold", fill: rgb("#0A3866"))[KATA PENGANTAR / PREFACE]
  ]
  v(1cm)

  grid(
    columns: (1fr, 1fr),
    gutter: 20pt,
    [
      #text(weight: "bold", size: 10pt)[KATA PENGANTAR] \ \
      Publikasi *Kabupaten Jember Dalam Angka #year* merupakan publikasi komprehensif tahunan yang diterbitkan oleh Badan Pusat Statistik (BPS) Kabupaten Jember. Buku ini menyajikan data statistik sektoral dan data dasar yang mencakup kondisi geografi, sosial demografi, dan ekonomi di Kabupaten Jember.
      
      #v(0.8cm)
      #align(right)[
        Jember, September #year \
        *Kepala BPS Kabupaten Jember*
      ]
    ],
    [
      #text(weight: "bold", style: "italic", size: 10pt, fill: rgb("#7F8C8D"))[PREFACE] \ \
      #text(style: "italic", fill: rgb("#475569"))[
        *Jember Regency in Figures #year* is an annual comprehensive publication published by BPS-Statistics of Jember Regency. This book presents sectoral statistical data and fundamental indicators covering geography, social demography, and economics in Jember Regency.
      ]
      
      #v(0.8cm)
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
