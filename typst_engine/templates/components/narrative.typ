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
