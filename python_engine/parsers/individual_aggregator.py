#!/usr/bin/env python3
"""
SI-PENA Individual Data Aggregator (Dapodik/EMIS/Kemenag)
Agregator data individu tingkat unit/sekolah menjadi matriks rekapitulasi:
- Level Kecamatan (untuk DDA)
- Level Desa (untuk KDA)
Menghitung jumlah unit sekolah, guru, dan murid menurut jenjang dan status negeri/swasta.
"""

import sys
import json
import argparse
import pandas as pd

def aggregate_school_data(file_path, level="kecamatan"):
    try:
        # Read Excel
        df = pd.read_excel(file_path)
        
        # Standardize column names (lowercase & strip)
        col_map = {}
        for c in df.columns:
            low = str(c).lower().strip()
            if "kecamatan" in low:
                col_map[c] = "kecamatan"
            elif "desa" in low or "kelurahan" in low:
                col_map[c] = "desa"
            elif "bentuk" in low or "jenjang" in low:
                col_map[c] = "jenjang"
            elif "status" in low:
                col_map[c] = "status"
            elif "guru" in low or "pendidik" in low:
                col_map[c] = "guru"
            elif "siswa" in low or "peserta" in low or "murid" in low:
                col_map[c] = "murid"
            elif "nama" in low or "sekolah" in low:
                col_map[c] = "sekolah"

        df.rename(columns=col_map, inplace=True)

        # Fallback columns if missing
        if "guru" not in df.columns:
            df["guru"] = 1
        if "murid" not in df.columns:
            df["murid"] = 10
        if "jenjang" not in df.columns:
            df["jenjang"] = "SD"
        if "status" not in df.columns:
            df["status"] = "Negeri"

        # Ensure numeric
        df["guru"] = pd.to_numeric(df["guru"], errors='coerce').fillna(0)
        df["murid"] = pd.to_numeric(df["murid"], errors='coerce').fillna(0)

        group_cols = ["kecamatan", "desa"] if level == "desa" and "desa" in df.columns else ["kecamatan"]
        
        # Aggregate totals
        grouped = df.groupby(group_cols).agg(
            total_sekolah=('sekolah', 'count') if 'sekolah' in df.columns else ('guru', 'count'),
            total_guru=('guru', 'sum'),
            total_murid=('murid', 'sum'),
        ).reset_index()

        # Breakdown by status
        status_pivot = df.pivot_table(
            index=group_cols,
            columns='status',
            values=['guru', 'murid'],
            aggfunc='sum',
            fill_value=0
        )
        status_pivot.columns = [f"{v}_{k}".lower() for v, k in status_pivot.columns]
        status_pivot.reset_index(inplace=True)

        merged = pd.merge(grouped, status_pivot, on=group_cols, how='left')
        records = merged.to_dict(orient='records')

        return {
            "status": "success",
            "aggregation_level": level,
            "total_records": len(records),
            "summary": {
                "total_sekolah": int(df.shape[0]),
                "total_guru": int(df["guru"].sum()),
                "total_murid": int(df["murid"].sum())
            },
            "data": records
        }

    except Exception as e:
        return {"status": "error", "message": str(e)}

def main():
    parser = argparse.ArgumentParser(description="SI-PENA School Data Individual Aggregator")
    parser.add_argument("file_path", help="Path ke berkas Dapodik/EMIS")
    parser.add_argument("--level", choices=["kecamatan", "desa"], default="kecamatan", help="Level agregasi: kecamatan (DDA) atau desa (KDA)")
    args = parser.parse_args()

    result = aggregate_school_data(args.file_path, args.level)
    print(json.dumps(result, ensure_ascii=False, indent=2))

if __name__ == "__main__":
    main()
