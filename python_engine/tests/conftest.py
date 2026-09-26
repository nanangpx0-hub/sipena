"""Konfigurasi pytest SI-PENA: akses modul worker Python tanpa instalasi paket."""
import pathlib
import sys

BASE = pathlib.Path(__file__).resolve().parents[1]
for sub in ("parsers", "calculators", "visualizers"):
    path = str(BASE / sub)
    if path not in sys.path:
        sys.path.insert(0, path)
