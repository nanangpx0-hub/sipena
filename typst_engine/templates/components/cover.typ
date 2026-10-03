// BPS Front Cover Component
#import "state.typ": section-name

#let front-cover(
  title: "",
  year: "",
  volume: "",
  catalog_no: "",
  pub_no: "",
  issn: "",
  bg_image_path: "",
  next_section: "",
) = [
  #set page(
    margin: (top: 0cm, bottom: 0cm, left: 0cm, right: 0cm),
    header: none,
    footer: none,
  )

  #if bg_image_path != "" [
    #place(top + left)[
      #image(bg_image_path, width: 100%, height: 100%, fit: "cover")
    ]
  ] else [
    #rect(
      width: 100%,
      height: 100%,
      fill: rgb("#0A3866"),
      inset: 0pt,
    )[
      #place(top + left)[
        #rect(
          width: 100%,
          height: 62%,
          fill: rgb("#062442"),
          inset: 2.2cm,
        )[
          // Header BPS Metadata
          #grid(
            columns: (1fr, auto),
            align: (left, right),
            [
              #text(size: 8pt, fill: rgb("#94A3B8"), weight: "bold")[BADAN PUSAT STATISTIK\ KABUPATEN JEMBER]
            ],
            [
              #if catalog_no != "" [
                #text(size: 8pt, fill: rgb("#94A3B8"))[No. Katalog: #catalog_no] \
              ]
              #if pub_no != "" [
                #text(size: 8pt, fill: rgb("#94A3B8"))[No. Publikasi: #pub_no]
              ]
            ],
          )

          #v(2.5cm)

          // Main Title
          #text(font: ("Roboto", "Arial"), size: 22pt, weight: "black", fill: white)[#upper(title)]
          
          #v(0.6cm)
          
          #text(font: ("Roboto", "Arial"), size: 13pt, weight: "bold", fill: rgb("#E67E22"))[TAHUN #year]
          #if volume != "" [
            #h(10pt)
            #text(size: 10pt, fill: rgb("#CBD5E1"))[VOLUME #volume]
          ]
        ]
      ]

      // Bottom Decorative Bar & ISSN
      #place(bottom + left)[
        #rect(
          width: 100%,
          height: 38%,
          fill: rgb("#0A3866"),
          inset: 2.2cm,
        )[
          #v(2.5cm)
          #grid(
            columns: (1fr, auto),
            align: (left + bottom, right + bottom),
            [
              #text(size: 10pt, weight: "bold", fill: white)[BADAN PUSAT STATISTIK KABUPATEN JEMBER] \
              #text(size: 7.5pt, fill: rgb("#94A3B8"))[Jl. Kalimantan No. 42 Jember 68121 - Jawa Timur]
            ],
            [
              #if issn != "" [
                #rect(
                  fill: white,
                  radius: 3pt,
                  inset: 5pt,
                  stroke: 1pt + rgb("#CBD5E1"),
                )[
                  #text(size: 7.5pt, weight: "bold", fill: black)[ISSN: #issn]
                ]
              ]
            ],
          )
        ]
      ]
    ]
  ]

  // Nama bagian halaman berikutnya harus diupdate sebelum pagebreak
  // agar header halaman tersebut sudah membawa nama yang benar.
  // Penomoran direset ke 0 di akhir cover sehingga halaman pertama
  // front matter bernomor "i" (romawi).
  #if next_section != "" [
    #section-name.update(next_section)
  ]
  #counter(page).update(0)
  #pagebreak()
]
