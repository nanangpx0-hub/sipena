// BPS Chapter Divider Component
#let chapter-divider(
  chapter_no: 1,
  title_id: "",
  title_en: "",
  highlight_label: "",
  highlight_val: "",
) = [
  #pagebreak(to: "odd")
  
  #v(3cm)
  
  // Chapter Number
  #align(left)[
    #text(font: ("Roboto", "Arial"), size: 54pt, weight: "black", fill: rgb("#E67E22"))[#str(chapter_no)]
  ]
  
  #v(-15pt)
  
  // Chapter Titles
  #align(left)[
    #text(font: ("Roboto", "Arial"), size: 20pt, weight: "bold", fill: rgb("#0A3866"))[#upper(title_id)]
    #if title_en != "" [
      \
      #v(4pt)
      #text(font: ("Roboto", "Arial"), size: 14pt, style: "italic", fill: rgb("#7F8C8D"))[#title_en]
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
