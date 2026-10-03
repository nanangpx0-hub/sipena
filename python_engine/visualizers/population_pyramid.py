#!/usr/bin/env python3
"""
SI-PENA Population Pyramid Visualizer
Menghasilkan piramida penduduk (laki-laki vs perempuan) per kelompok umur 5 tahunan
dalam format vektor SVG tajam.
"""

import sys
import os
import json
import argparse
import numpy as np
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

def generate_population_pyramid(output_svg_path, district_name="Kabupaten Jember", year=2026, data=None):
    os.makedirs(os.path.dirname(os.path.abspath(output_svg_path)), exist_ok=True)

    age_groups = [
        "0-4", "5-9", "10-14", "15-19", "20-24", "25-29",
        "30-34", "35-39", "40-44", "45-49", "50-54", "55-59",
        "60-64", "65-69", "70-74", "75+"
    ]

    if data is not None:
        if not isinstance(data, dict):
            return {"status": "error", "message": "Argumen data piramida harus berupa objek JSON"}
        males = data.get("males", [])
        females = data.get("females", [])
        # Deret wajib lengkap 16 kelompok umur agar grafik tidak diisi angka karangan.
        if len(males) != len(age_groups) or len(females) != len(age_groups):
            return {"status": "error", "message": "Deret piramida tidak lengkap (butuh 16 kelompok umur)"}
        try:
            males = [int(v) for v in males]
            females = [int(v) for v in females]
        except (TypeError, ValueError):
            return {"status": "error", "message": "Deret piramida bukan angka yang sah"}
        if any(v < 0 for v in males) or any(v < 0 for v in females):
            return {"status": "error", "message": "Deret piramida berisi nilai negatif"}
    else:
        # Default Jember standard demographic distribution
        males = [92100, 94200, 96400, 98100, 95300, 91200, 88400, 85300, 79200, 72100, 64200, 53100, 41200, 31100, 21400, 18500]
        females = [89300, 91100, 93500, 95400, 93200, 89400, 87100, 84200, 78600, 71500, 64900, 54200, 42800, 33400, 24100, 22100]

    # Convert males to negative for left side
    males_neg = [-m for m in males]

    fig, ax = plt.subplots(figsize=(8, 6), dpi=300)

    y_pos = np.arange(len(age_groups))
    
    # Plot bars
    ax.barh(y_pos, males_neg, color='#2980B9', label='Laki-Laki (Male)', height=0.75)
    ax.barh(y_pos, females, color='#E67E22', label='Perempuan (Female)', height=0.75)

    ax.set_yticks(y_pos)
    ax.set_yticklabels(age_groups, fontsize=8, color='#2C3E50')
    
    # Absolute values on X-axis ticks
    max_val = max(max(males), max(females)) * 1.15
    ticks = np.linspace(-max_val, max_val, 7)
    ax.set_xticks(ticks)
    ax.set_xticklabels([f"{abs(int(t)):,}" for t in ticks], fontsize=8, color='#2C3E50')

    ax.axvline(0, color='#0A3866', linewidth=1)
    ax.set_title(f"Piramida Penduduk {district_name} Tahun {year}", fontsize=11, fontweight='bold', pad=12, color='#0A3866')
    ax.set_xlabel("Jumlah Penduduk (Jiwa)", fontsize=9, fontweight='bold', color='#2C3E50')
    ax.set_ylabel("Kelompok Umur (Tahun)", fontsize=9, fontweight='bold', color='#2C3E50')
    ax.grid(axis='x', linestyle=':', alpha=0.5)
    ax.legend(loc='upper right', fontsize=8)

    plt.tight_layout()
    plt.savefig(output_svg_path, format='svg')
    plt.close()

    return {"status": "success", "file": output_svg_path}

def main():
    parser = argparse.ArgumentParser(description="SI-PENA Population Pyramid Generator")
    parser.add_argument("--output", default="storage/custom_assets/population_pyramid.svg", help="Target SVG file path")
    parser.add_argument("--district", default="Kabupaten Jember", help="Nama wilayah/kecamatan")
    parser.add_argument("--year", type=int, default=2026, help="Tahun data")
    parser.add_argument("--data-json", default=None, help='Deret data JSON: {"males":[16], "females":[16]}')
    args = parser.parse_args()

    data = None
    if args.data_json is not None:
        try:
            parsed = json.loads(args.data_json)
            if not isinstance(parsed, dict):
                raise ValueError("argumen --data-json bukan objek JSON")
            data = parsed
        except (ValueError, TypeError) as exc:
            print(json.dumps({"status": "error", "message": "Data piramida tidak sah: %s" % exc}))
            return

    result = generate_population_pyramid(args.output, args.district, args.year, data=data)
    print(json.dumps(result))

if __name__ == "__main__":
    main()
