#import "components/cover.typ": front-cover
#import "components/divider.typ": chapter-divider
#import "components/tables.typ": bps-table

#let skd-document(
  title: "Analisis Hasil Survei Kebutuhan Data BPS Kabupaten Jember 2026",
  year: "2026",
  volume: "9",
  catalog_no: "1305011.3509",
  pub_no: "35090.2602",
  issn: "2548-8120",
  ikk_score: "90.96",
  ipak_score: "90.33",
  body,
) = {
  // Page setup for B5 BPS standard (18.2cm x 25.7cm)
  set document(title: title, author: "BPS Kabupaten Jember")
  set text(font: ("Roboto", "Liberation Sans", "Arial"), size: 9.5pt, fill: rgb("#2C3E50"))
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
        #text(size: 8.5pt, fill: rgb("#1E3A8A"))[Predikat: Sangat Baik (Skala 100)]
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
        #text(size: 8.5pt, fill: rgb("#064E3B"))[Predikat: Bersih dari Korupsi (Skala 100)]
      ]
    ]
  )

  v(1cm)
  body
}
