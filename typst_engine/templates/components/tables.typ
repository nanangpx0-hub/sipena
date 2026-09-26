// BPS Standard Three-Line Table Component.
// Seluruh blok (judul, tabel, catatan, sumber) dibungkus figure(kind: table)
// agar judul tabel otomatis masuk ke DAFTAR TABEL melalui outline().
#let bps-table(
  title_id: "",
  title_en: "",
  table_num: "",
  col_widths: (),
  headers_id: (),
  headers_en: (),
  data_rows: (),
  source_text: "BPS Kabupaten Jember",
  note_text: "",
) = [
  #v(8pt)
  #figure(
    kind: table,
    supplement: [Tabel],
    numbering: none,
    caption: [
      #text(weight: "bold", size: 9pt, fill: rgb("#0A3866"))[Tabel #table_num #title_id]
      #if title_en != "" [
        \ #text(style: "italic", size: 8pt, fill: rgb("#7F8C8D"))[Table #table_num #title_en]
      ]
    ],
  )[
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
    #if note_text != "" [
      #v(3pt)
      #text(size: 7pt, fill: rgb("#7F8C8D"))[*Catatan / Note:* #note_text]
    ]
    #v(3pt)
    #text(size: 7pt, fill: rgb("#7F8C8D"))[*Sumber / Source:* #source_text]
  ]
  #v(4pt)
]
