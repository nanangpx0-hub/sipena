#!/usr/bin/env python3
"""
SI-PENA Climate & Rainfall Chart Visualizer
Menghasilkan grafik batang curah hujan bulanan (mm) dan hari hujan dalam format vektor SVG tajam.
"""

import sys
import os
import json
import argparse
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

def generate_climate_chart(output_svg_path, district_name="Kabupaten Jember", year=2026, data=None):
    os.makedirs(os.path.dirname(os.path.abspath(output_svg_path)), exist_ok=True)

    months = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agt", "Sep", "Okt", "Nov", "Des"]

    if data is not None:
        if not isinstance(data, dict):
            return {"status": "error", "message": "Argumen data grafik iklim harus berupa objek JSON"}
        rainfall = data.get("rainfall", [])
        rain_days = data.get("rain_days", [])
        # Deret wajib lengkap 12 bulan agar grafik tidak diisi angka karangan.
        if len(rainfall) != len(months) or len(rain_days) != len(months):
            return {"status": "error", "message": "Deret curah hujan/hari hujan tidak lengkap (butuh 12 bulan)"}
        try:
            rainfall = [float(v) for v in rainfall]
            rain_days = [float(v) for v in rain_days]
        except (TypeError, ValueError):
            return {"status": "error", "message": "Deret curah hujan/hari hujan bukan angka yang sah"}
        if any(v < 0 for v in rainfall) or any(v < 0 for v in rain_days) or any(v > 31 for v in rain_days):
            return {"status": "error", "message": "Deret curah hujan/hari hujan di luar rentang wajar"}
    else:
        # Standard tropical monsoon pattern for Jember
        rainfall = [385, 340, 290, 195, 110, 65, 40, 25, 35, 95, 230, 360]
        rain_days = [22, 20, 18, 14, 9, 6, 4, 2, 3, 8, 17, 21]

    fig, ax1 = plt.subplots(figsize=(8, 5), dpi=300)

    # Bar chart for rainfall
    color_bar = '#2980B9'
    ax1.set_xlabel('Bulan (Month)', fontsize=9, fontweight='bold', color='#2C3E50')
    ax1.set_ylabel('Curah Hujan (mm)', color=color_bar, fontsize=9, fontweight='bold')
    bars = ax1.bar(months, rainfall, color=color_bar, alpha=0.85, width=0.55, label='Curah Hujan (mm)')
    ax1.tick_params(axis='y', labelcolor=color_bar)
    ax1.set_ylim(0, max(rainfall) * 1.25)

    # Secondary axis for rainy days
    ax2 = ax1.twinx()
    color_line = '#E67E22'
    ax2.set_ylabel('Hari Hujan (Hari)', color=color_line, fontsize=9, fontweight='bold')
    lines = ax2.plot(months, rain_days, color=color_line, marker='o', linewidth=2.5, label='Jumlah Hari Hujan')
    ax2.tick_params(axis='y', labelcolor=color_line)
    ax2.set_ylim(0, 31)

    plt.title(f"Rata-Rata Curah Hujan dan Hari Hujan di {district_name} Tahun {year}", fontsize=11, fontweight='bold', pad=12, color='#0A3866')
    ax1.grid(axis='y', linestyle=':', alpha=0.5)

    plt.tight_layout()
    plt.savefig(output_svg_path, format='svg')
    plt.close()

    return {"status": "success", "file": output_svg_path}

def main():
    parser = argparse.ArgumentParser(description="SI-PENA Climate & Rainfall Chart Generator")
    parser.add_argument("--output", default="storage/custom_assets/climate_chart.svg", help="Target SVG file path")
    parser.add_argument("--district", default="Kabupaten Jember", help="Nama wilayah/kecamatan")
    parser.add_argument("--year", type=int, default=2026, help="Tahun data")
    parser.add_argument("--data-json", default=None, help='Deret data JSON: {"rainfall":[12], "rain_days":[12]}')
    args = parser.parse_args()

    data = None
    if args.data_json is not None:
        try:
            parsed = json.loads(args.data_json)
            if not isinstance(parsed, dict):
                raise ValueError("argumen --data-json bukan objek JSON")
            data = parsed
        except (ValueError, TypeError) as exc:
            print(json.dumps({"status": "error", "message": "Data grafik iklim tidak sah: %s" % exc}))
            return

    result = generate_climate_chart(args.output, args.district, args.year, data=data)
    print(json.dumps(result))

if __name__ == "__main__":
    main()
