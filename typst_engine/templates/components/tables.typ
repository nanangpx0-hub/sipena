// BPS Standard Three-Line Table Component
#let bps-table(
  title_id: "",
  title_en: "",
  table_num: "",
  col_widths: (),
  headers_id: (),
  headers_en: (),
  data_rows: (),
  source_text: "BPS Kabupaten Jember",
) = [
  #v(8pt)
  // Table Title Block (Bilingual)
  #block(width: 100%, breakable: false)[
    #text(weight: "bold", size: 9pt, fill: rgb("#0A3866"))[Tabel #table_num #h(4pt) #title_id]
    #if title_en != "" [
      \ #text(style: "italic", size: 8pt, fill: rgb("#7F8C8D"))[Table #table_num #h(4pt) #title_en]
    ]
  ]
  #v(4pt)

  // Generate numbered columns (1), (2), (3)...
  #{
    let col_count = col_widths.len()
    let col_numbers = range(1, col_count + 1).map(n => "(" + str(n) + ")")

    align(center)[
      #table(
        columns: col_widths,
        stroke: none,
        align: (col, row) => if col == 0 { left + horizon } else { right + horizon },
        inset: (x: 4pt, y: 3.5pt),
        table.header(
          repeat: true,
          table.hline(stroke: 1.2pt + rgb("#2C3E50")),
          ..headers_id.map(h => text(weight: "bold", size: 8pt, fill: rgb("#2C3E50"), align(center)[#h])),
          ..if headers_en.len() > 0 {
            headers_en.map(h => text(style: "italic", size: 7.5pt, fill: rgb("#7F8C8D"), align(center)[#h]))
          } else { () },
          table.hline(stroke: 0.5pt + rgb("#BDC3C7")),
          ..col_numbers.map(n => text(size: 7pt, fill: rgb("#7F8C8D"), align(center)[#n])),
          table.hline(stroke: 0.8pt + rgb("#2C3E50")),
        ),
        ..data_rows.map(cell => text(size: 8pt, fill: rgb("#2C3E50"))[#cell]),
        table.hline(stroke: 1.2pt + rgb("#2C3E50")),
      )
    ]
  }

  #v(2pt)
  #block(width: 100%)[
    #text(size: 7pt, fill: rgb("#7F8C8D"))[
      *Sumber / Source:* #source_text
    ]
  ]
  #v(8pt)
]
