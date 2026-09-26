// BPS Chapter Divider Component
// Judul bab memakai heading() agar otomatis masuk DAFTAR ISI,
// sekaligus memperbarui nama bagian pada header halaman.
#import "state.typ": section-name

#let chapter-divider(
  chapter_no: 1,
  title_id: "",
  title_en: "",
  highlight_label: "",
  highlight_val: "",
  first: false,
) = [
  // Nama bab harus diupdate sebelum pagebreak agar header halaman
  // pembatas sudah memuat nama bab yang benar.
  #section-name.update(upper(title_id))
  #if not first [
    #pagebreak(to: "odd")
  ]

  #v(3cm)

  // Chapter Number
  #align(left)[
    #text(font: ("Roboto", "Arial"), size: 54pt, weight: "black", fill: rgb("#E67E22"))[#str(chapter_no)]
  ]

  #v(-15pt)

  // Chapter Titles (heading level 1 -> tercantum pada Daftar Isi)
  #align(left)[
    #show heading.where(level: 1): set text(
      size: 20pt,
      weight: "bold",
      fill: rgb("#0A3866"),
    )
    #heading(level: 1)[
      #upper(title_id)#if title_en != "" [ \/ #title_en ]
    ]
  ]

  #v(1.5cm)

  // Key Metric Highlight Card (if provided)
  #if highlight_label != "" and highlight_val != "" [
    #rect(
      width: 100%,
      radius: 6pt,
      fill: rgb("#FFF7ED"),
      stroke: 1pt + rgb("#FED7AA"),
      inset: 14pt,
    )[
      #align(left)[
        #text(size: 9pt, weight: "bold", fill: rgb("#9A3412"))[INDIKATOR KUNCI / KEY FIGURES]
        #v(6pt)
        #text(size: 20pt, weight: "black", fill: rgb("#D35400"))[#highlight_val]
        #v(2pt)
        #text(size: 10pt, weight: "medium", fill: rgb("#7C2D12"))[#highlight_label]
      ]
    ]
  ]

  #pagebreak()
]
