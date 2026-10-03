#!/usr/bin/env python3
"""
SI-PENA SKD (Survei Kebutuhan Data) Analytical Engine
Formula Resmi Peraturan Menteri PANRB No. 14/2017 & Standar BPS RI:
- Rata-rata Kepuasan (X_bar) & Kepentingan (Y_bar)
- Penimbang Atribut (w_i)
- Indeks Kepuasan Konsumen (IKK skala 100) & Mutu Pelayanan
- Indeks Persepsi Anti Korupsi (IPAK skala 100)
- Gap Analysis (X_bar - Y_bar) & Tingkat Kesesuaian (TK = X/Y * 100%)
- Diagram Kuadran Cartesius IPA (Kuadran A, B, C, D)
- Ekspor Plot SVG Vektor Tajam & JSON Metrics
"""

import sys
import os
import json
import argparse
import numpy as np
import pandas as pd
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

# 12 Standar Atribut Pelayanan Publik BPS (VKD Blok III)
SERVICE_ATTRIBUTES = [
    {"code": "U1", "name": "Kesesuaian Persyaratan Layanan", "name_en": "Service Requirements"},
    {"code": "U2", "name": "Kemudahan Prosedur Layanan", "name_en": "Service Procedures"},
    {"code": "U3", "name": "Kecepatan Waktu Pelayanan", "name_en": "Service Time & Speed"},
    {"code": "U4", "name": "Biaya/Tarif Bebas Pungli (Gratis)", "name_en": "Cost/Tariff Zero Fee"},
    {"code": "U5", "name": "Kesesuaian Produk Layanan", "name_en": "Product Specification"},
    {"code": "U6", "name": "Kompetensi Petugas Pelayanan", "name_en": "Officer Competency"},
    {"code": "U7", "name": "Perilaku Sopan & Ramah Petugas", "name_en": "Officer Courtesy & Politeness"},
    {"code": "U8", "name": "Kualitas Sarana & Prasarana PST", "name_en": "Facilities & Infrastructure"},
    {"code": "U9", "name": "Penanganan Pengaduan Konsumen", "name_en": "Complaints Handling"},
    {"code": "U10", "name": "Aksesibilitas Informasi & Website", "name_en": "Data Accessibility & Website"},
    {"code": "U11", "name": "Kemutakhiran Data & Ragam Pilihan", "name_en": "Data Up-to-dateness & Variety"},
    {"code": "U12", "name": "Kenyamanan Ruang Pelayanan", "name_en": "Service Room Comfort"},
]

def generate_sample_vkd():
    """Menghasilkan dummy respons VKD jika file input tidak disediakan."""
    np.random.seed(42)
    n_resp = 100
    data = {}
    for attr in SERVICE_ATTRIBUTES:
        # X: Kepuasan (skala 1-4), Y: Kepentingan (skala 1-4)
        data[f"{attr['code']}_X"] = np.random.choice([3, 4], size=n_resp, p=[0.35, 0.65])
        data[f"{attr['code']}_Y"] = np.random.choice([3, 4], size=n_resp, p=[0.25, 0.75])
    return pd.DataFrame(data)

# Profil demografi konsumen PST. Kolom VKD bersifat opsional: bila berkas
# kuesioner tidak memuatnya, blok profil TIDAK disertakan dan sistem
# menampilkan kondisi "belum ada data" (AGENTS.md §1.4 - tanpa angka tebakan).
DEMOGRAPHIC_FIELDS = [
    {
        "key": "gender",
        "label": "Jenis Kelamin",
        "label_en": "Gender",
        "candidates": ["jenis_kelamin", "gender", "jk", "sex", "Lk", "Perempuan"],
    },
    {
        "key": "age_group",
        "label": "Kelompok Usia",
        "label_en": "Age Group",
        "candidates": ["kelompok_usia", "usia", "age", "umur", "rentang_usia"],
    },
    {
        "key": "education",
        "label": "Pendidikan Terakhir",
        "label_en": "Education Level",
        "candidates": ["pendidikan", "education", "jenjang_pendidikan", "tingkat_pendidikan"],
    },
    {
        "key": "occupation",
        "label": "Profesi / Pekerjaan",
        "label_en": "Occupation",
        "candidates": ["profesi", "occupation", "pekerjaan", "jabatan", "sektor"],
    },
    {
        "key": "respondent_type",
        "label": "Jenis Konsumen",
        "label_en": "Consumer Segment",
        "candidates": ["jenis_konsumen", "segmen", "segment", "tipe_konsumen", "kategori_konsumen"],
    },
]


def build_respondent_profile(df):
    """
    Ringkas profil demografi responden dari kolom VKD yang bersifat opsional.

    Mengembalikan dict berisi kunci dimensi yang memuat frekuensi (label, jumlah,
    persentase) serta total responden. Dimensi tanpa kolom yang cocok dilewati
    sepenuhnya, bukan diisi nol, agar halaman tidak pernah menampilkan angka
    rekaan.
    """
    total_respondents = int(len(df))
    dimensions = []

    for field in DEMOGRAPHIC_FIELDS:
        column = None
        for candidate in field["candidates"]:
            if candidate in df.columns:
                column = candidate
                break

        if column is None:
            # Pencocokan longgar: kolom berlabel mirip namun tidak persis sama.
            for actual in df.columns:
                normalized = str(actual).strip().lower().replace(" ", "_")
                if any(candidate.lower() in normalized for candidate in field["candidates"]):
                    column = actual
                    break

        if column is None:
            continue

        series = df[column].dropna().astype(str).str.strip()
        series = series[series != ""]
        if series.empty:
            continue

        counts = series.value_counts()
        breakdown = [
            {
                "label": str(label),
                "count": int(count),
                "percent": round(float(count) * 100.0 / total_respondents, 2) if total_respondents else 0.0,
            }
            for label, count in counts.items()
        ]

        dimensions.append({
            "key": field["key"],
            "label": field["label"],
            "label_en": field["label_en"],
            "column": str(column),
            "breakdown": breakdown,
        })

    if not dimensions:
        return None

    return {
        "total_respondents": total_respondents,
        "dimensions": dimensions,
    }

def calculate_skd_metrics(df, output_svg_path=None):
    results = []
    x_means = []
    y_means = []
    missing_columns = []

    for attr in SERVICE_ATTRIBUTES:
        c = attr["code"]
        x_col = f"{c}_X" if f"{c}_X" in df.columns else [col for col in df.columns if c in col and ('x' in col.lower() or 'puas' in col.lower())]
        y_col = f"{c}_Y" if f"{c}_Y" in df.columns else [col for col in df.columns if c in col and ('y' in col.lower() or 'penting' in col.lower())]

        col_x_name = x_col[0] if isinstance(x_col, list) and x_col else (x_col if isinstance(x_col, str) else None)
        col_y_name = y_col[0] if isinstance(y_col, list) and y_col else (y_col if isinstance(y_col, str) else None)

        # STRICT MODE: tanpa kolom default diam-diam. Kuesioner yang tidak
        # lengkap wajib dilaporkan sebagai error agar angka resmi tidak pernah
        # tercampur nilai buatan sistem.
        if not col_x_name or col_x_name not in df.columns:
            missing_columns.append(f"{c}_X (kepuasan)")
            continue
        if not col_y_name or col_y_name not in df.columns:
            missing_columns.append(f"{c}_Y (kepentingan)")
            continue

        series_x = pd.to_numeric(df[col_x_name], errors='coerce').dropna()
        series_y = pd.to_numeric(df[col_y_name], errors='coerce').dropna()

        if series_x.empty or series_y.empty:
            missing_columns.append(f"{c} (nilai tidak numerik/kosong)")
            continue

        mean_x = float(series_x.mean())
        mean_y = float(series_y.mean())

        gap = mean_x - mean_y
        tk = (mean_x / mean_y * 100) if mean_y > 0 else 100.0

        x_means.append(mean_x)
        y_means.append(mean_y)

        results.append({
            "code": c,
            "name": attr["name"],
            "name_en": attr["name_en"],
            "mean_satisfaction": round(mean_x, 3),
            "mean_importance": round(mean_y, 3),
            "gap": round(gap, 3),
            "conformity_rate": round(tk, 2),
        })

    if missing_columns:
        return {
            "status": "error",
            "message": "Kuesioner VKD tidak memuat kolom yang diperlukan: " + "; ".join(missing_columns),
            "missing_columns": missing_columns,
        }

    if not results:
        return {"status": "error", "message": "Seluruh kolom atribut layanan (U1-U12) tidak ditemukan pada berkas VKD."}

    # Total and Grand Averages
    grand_mean_x = float(np.mean(x_means))
    grand_mean_y = float(np.mean(y_means))

    # Permenpan RB IKK calculation: (Grand Mean X / 4) * 100
    # or sum(Mean_X * (1/12)) * 25
    ikk_score = (grand_mean_x / 4.0) * 100.0
    
    # Mutu Pelayanan
    if ikk_score >= 88.31:
        mutu = "A (Sangat Baik)"
    elif ikk_score >= 76.61:
        mutu = "B (Baik)"
    elif ikk_score >= 65.00:
        mutu = "C (Kurang Baik)"
    else:
        mutu = "D (Tidak Baik)"

    # IPAK estimation from Integrity & Clean Attributes (U4, U6, U7)
    ipak_attrs = [r for r in results if r["code"] in ["U4", "U6", "U7"]]
    if not ipak_attrs:
        return {"status": "error", "message": "Atribut integritas U4/U6/U7 tidak ditemukan sehingga IPAK tidak dapat dihitung."}
    ipak_raw = np.mean([r["mean_satisfaction"] for r in ipak_attrs])
    ipak_score = (ipak_raw / 4.0) * 100.0

    # Determine Cartesian Quadrants
    quadrants = {"A": [], "B": [], "C": [], "D": []}
    for item in results:
        x = item["mean_satisfaction"]
        y = item["mean_importance"]
        if x < grand_mean_x and y >= grand_mean_y:
            item["quadrant"] = "A"
            item["quadrant_desc"] = "Prioritas Utama (High Importance, Low Satisfaction)"
            quadrants["A"].append(item["code"])
        elif x >= grand_mean_x and y >= grand_mean_y:
            item["quadrant"] = "B"
            item["quadrant_desc"] = "Pertahankan Prestasi (High Importance, High Satisfaction)"
            quadrants["B"].append(item["code"])
        elif x < grand_mean_x and y < grand_mean_y:
            item["quadrant"] = "C"
            item["quadrant_desc"] = "Prioritas Rendah (Low Importance, Low Satisfaction)"
            quadrants["C"].append(item["code"])
        else:
            item["quadrant"] = "D"
            item["quadrant_desc"] = "Berlebihan (Low Importance, High Satisfaction)"
            quadrants["D"].append(item["code"])

    # Plot Cartesian Diagram to SVG
    svg_relative_path = None
    if output_svg_path:
        os.makedirs(os.path.dirname(os.path.abspath(output_svg_path)), exist_ok=True)
        fig, ax = plt.subplots(figsize=(8, 6), dpi=300)
        
        # Quadrant background colors
        x_min, x_max = grand_mean_x - 0.45, grand_mean_x + 0.45
        y_min, y_max = grand_mean_y - 0.45, grand_mean_y + 0.45

        ax.axvline(grand_mean_x, color='#E67E22', linestyle='--', linewidth=1.5, label=f'Rata-rata Kepuasan ({grand_mean_x:.2f})')
        ax.axhline(grand_mean_y, color='#0A3866', linestyle='--', linewidth=1.5, label=f'Rata-rata Kepentingan ({grand_mean_y:.2f})')

        # Scatter points
        for item in results:
            ax.scatter(item["mean_satisfaction"], item["mean_importance"], color='#2980B9', s=90, zorder=5)
            ax.annotate(
                item["code"],
                (item["mean_satisfaction"], item["mean_importance"]),
                textcoords="offset points",
                xytext=(5, 5),
                ha='left',
                fontsize=9,
                fontweight='bold',
                color='#1E293B'
            )

        # Quadrant Labels
        ax.text(x_min + 0.05, y_max - 0.05, 'KUADRAN A\n(Prioritas Utama)', fontsize=10, fontweight='bold', color='#DC2626', alpha=0.7)
        ax.text(x_max - 0.05, y_max - 0.05, 'KUADRAN B\n(Pertahankan Prestasi)', fontsize=10, fontweight='bold', color='#16A34A', ha='right', alpha=0.7)
        ax.text(x_min + 0.05, y_min + 0.05, 'KUADRAN C\n(Prioritas Rendah)', fontsize=10, fontweight='bold', color='#CA8A04', alpha=0.7)
        ax.text(x_max - 0.05, y_min + 0.05, 'KUADRAN D\n(Cenderung Berlebihan)', fontsize=10, fontweight='bold', color='#6B7280', ha='right', alpha=0.7)

        ax.set_xlim(x_min, x_max)
        ax.set_ylim(y_min, y_max)
        ax.set_title("Diagram Kartesius Importance-Performance Analysis (IPA) SKD BPS Jember", fontsize=11, fontweight='bold', pad=12, color='#0A3866')
        ax.set_xlabel("Tingkat Kepuasan / Performance (X)", fontsize=10, fontweight='bold', color='#2C3E50')
        ax.set_ylabel("Tingkat Kepentingan / Importance (Y)", fontsize=10, fontweight='bold', color='#2C3E50')
        ax.grid(True, linestyle=':', alpha=0.6)
        ax.legend(loc='lower left', fontsize=8)

        plt.tight_layout()
        plt.savefig(output_svg_path, format='svg')
        plt.close()
        svg_relative_path = output_svg_path

    return {
        "status": "success",
        "data_source": "file",
        "grand_mean_satisfaction": round(grand_mean_x, 3),
        "grand_mean_importance": round(grand_mean_y, 3),
        "ikk_score": round(ikk_score, 2),
        "mutu_pelayanan": mutu,
        "ipak_score": round(min(ipak_score, 100.0), 2),
        "quadrants": quadrants,
        "cartesian_svg_path": svg_relative_path,
        "attributes": results,
        "respondent_profile": build_respondent_profile(df),
    }

def main():
    parser = argparse.ArgumentParser(description="SI-PENA SKD Analytical & Cartesian Engine")
    parser.add_argument("--file", help="Path ke berkas mentah kuesioner VKD (Excel/CSV)")
    parser.add_argument("--output-svg", default="storage/custom_assets/skd_cartesian.svg", help="Path target penyimpanan plot SVG")
    parser.add_argument("--sample", action="store_true",
                        help="HANYA untuk demo/pengujian: hasilkan data contoh acak (angka BUKAN hasil survei resmi)")
    args = parser.parse_args()

    if args.file and os.path.exists(args.file):
        ext = os.path.splitext(args.file)[1].lower()
        df = pd.read_excel(args.file) if ext in ('.xlsx', '.xls') else pd.read_csv(args.file)
        data_source = "file"
    elif args.sample:
        df = generate_sample_vkd()
        data_source = "sample"
    else:
        # Tanpa berkas dan tanpa --sample: JANGAN menebak angka.
        print(json.dumps({
            "status": "error",
            "message": "Berkas kuesioner VKD tidak ditemukan. Unggah berkas .xlsx/.csv terlebih dahulu "
                       "(atau gunakan --sample hanya untuk demo).",
        }, ensure_ascii=False, indent=2))
        sys.exit(0)

    result = calculate_skd_metrics(df, args.output_svg)
    if result.get("status") == "success":
        result["data_source"] = data_source
    print(json.dumps(result, ensure_ascii=False, indent=2))

if __name__ == "__main__":
    main()
