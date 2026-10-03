"""Unit test untuk Visualizer (Climate Chart, Population Pyramid) dan Individual Aggregator."""
import json
import os
import pathlib
import subprocess
import sys
import openpyxl
import pytest
import individual_aggregator as ia
import climate_chart as cc
import population_pyramid as pp


def test_individual_aggregator_kecamatan_level(tmp_path):
    excel_path = tmp_path / "schools.xlsx"
    wb = openpyxl.Workbook()
    ws = wb.active
    ws.title = "Dapodik"
    ws.append(["Nama Sekolah", "Kecamatan", "Desa", "Jenjang", "Status", "Guru", "Murid"])
    ws.append(["SDN 1 Cakru", "Kencong", "Cakru", "SD", "Negeri", 12, 240])
    ws.append(["SDN 2 Cakru", "Kencong", "Cakru", "SD", "Negeri", 10, 180])
    ws.append(["SMPN 1 Gumukmas", "Gumukmas", "Menampu", "SMP", "Negeri", 25, 450])
    ws.append(["SMAS PGRI Gumukmas", "Gumukmas", "Bagorejo", "SMA", "Swasta", 15, 200])
    wb.save(excel_path)

    res = ia.aggregate_school_data(str(excel_path), level="kecamatan")
    assert res["status"] == "success"
    assert res["aggregation_level"] == "kecamatan"
    assert res["summary"]["total_sekolah"] == 4
    assert res["summary"]["total_guru"] == 62
    assert res["summary"]["total_murid"] == 1070
    assert len(res["data"]) == 2


def test_individual_aggregator_desa_level(tmp_path):
    excel_path = tmp_path / "schools_desa.xlsx"
    wb = openpyxl.Workbook()
    ws = wb.active
    ws.title = "Dapodik"
    ws.append(["Nama Sekolah", "Kecamatan", "Desa", "Jenjang", "Status", "Guru", "Murid"])
    ws.append(["SDN 1 Cakru", "Kencong", "Cakru", "SD", "Negeri", 12, 240])
    ws.append(["SDN 2 Cakru", "Kencong", "Cakru", "SD", "Negeri", 10, 180])
    ws.append(["SDN 1 Kencong", "Kencong", "Kencong", "SD", "Negeri", 14, 300])
    wb.save(excel_path)

    res = ia.aggregate_school_data(str(excel_path), level="desa")
    assert res["status"] == "success"
    assert res["aggregation_level"] == "desa"
    assert len(res["data"]) == 2  # Cakru and Kencong


def test_individual_aggregator_invalid_file(tmp_path):
    res = ia.aggregate_school_data(str(tmp_path / "non_existent.xlsx"))
    assert res["status"] == "error"
    assert "message" in res


def test_climate_chart_generation(tmp_path):
    out_svg = tmp_path / "test_climate.svg"
    res = cc.generate_climate_chart(str(out_svg), district_name="Kecamatan Kencong", year=2026)
    assert res["status"] == "success"
    assert os.path.exists(out_svg)
    assert os.path.getsize(out_svg) > 500
    with open(out_svg, "r", encoding="utf-8") as f:
        content = f.read()
    assert "<svg" in content
    assert "Kencong" in content


def test_population_pyramid_generation(tmp_path):
    out_svg = tmp_path / "test_pyramid.svg"
    res = pp.generate_population_pyramid(str(out_svg), district_name="Kecamatan Kencong", year=2026)
    assert res["status"] == "success"
    assert os.path.exists(out_svg)
    assert os.path.getsize(out_svg) > 500
    with open(out_svg, "r", encoding="utf-8") as f:
        content = f.read()
    assert "<svg" in content
    assert "Kencong" in content

# ---------------------------------------------------------------------------
# Uji --data-json (seri data ingesti riil, bukan pola bawaan)
# ---------------------------------------------------------------------------
VISUALIZERS = pathlib.Path(__file__).resolve().parents[1] / "visualizers"


def _last_json(stdout):
    for line in reversed(stdout.splitlines()):
        line = line.strip()
        if line.startswith("{"):
            return json.loads(line)
    raise AssertionError("tidak ada keluaran JSON: " + stdout)


def test_climate_chart_with_ingested_series(tmp_path):
    out_svg = tmp_path / "climate_data.svg"
    data = {"rainfall": [100.0] * 12, "rain_days": [10] * 12}
    res = cc.generate_climate_chart(str(out_svg), district_name="Kecamatan Kencong", year=2026, data=data)
    assert res["status"] == "success"
    assert os.path.exists(out_svg)
    with open(out_svg, "r", encoding="utf-8") as f:
        assert "<svg" in f.read()


def test_climate_chart_rejects_incomplete_or_invalid_series(tmp_path):
    incomplete = {"rainfall": [1] * 5, "rain_days": [1] * 12}
    assert cc.generate_climate_chart(str(tmp_path / "a.svg"), data=incomplete)["status"] == "error"

    non_numeric = {"rainfall": ["n/a"] * 12, "rain_days": [1] * 12}
    assert cc.generate_climate_chart(str(tmp_path / "b.svg"), data=non_numeric)["status"] == "error"

    out_of_range = {"rainfall": [-1] * 12, "rain_days": [1] * 12}
    assert cc.generate_climate_chart(str(tmp_path / "c.svg"), data=out_of_range)["status"] == "error"


def test_population_pyramid_with_ingested_series(tmp_path):
    out_svg = tmp_path / "pyramid_data.svg"
    data = {"males": [100] * 16, "females": [90] * 16}
    res = pp.generate_population_pyramid(str(out_svg), district_name="Kecamatan Kencong", year=2026, data=data)
    assert res["status"] == "success"
    assert os.path.exists(out_svg)
    with open(out_svg, "r", encoding="utf-8") as f:
        assert "<svg" in f.read()


def test_population_pyramid_rejects_incomplete_or_invalid_series(tmp_path):
    incomplete = {"males": [100] * 4, "females": [90] * 16}
    assert pp.generate_population_pyramid(str(tmp_path / "a.svg"), data=incomplete)["status"] == "error"

    negative = {"males": [-1] * 16, "females": [90] * 16}
    assert pp.generate_population_pyramid(str(tmp_path / "b.svg"), data=negative)["status"] == "error"

    non_numeric = {"males": ["x"] * 16, "females": [90] * 16}
    assert pp.generate_population_pyramid(str(tmp_path / "c.svg"), data=non_numeric)["status"] == "error"


def test_climate_chart_cli_reads_data_json(tmp_path):
    out_svg = tmp_path / "cli_climate.svg"
    payload = json.dumps({"rainfall": [120] * 12, "rain_days": [12] * 12})
    proc = subprocess.run(
        [sys.executable, str(VISUALIZERS / "climate_chart.py"), "--output", str(out_svg),
         "--district", "Kecamatan Kencong", "--year", "2026", "--data-json", payload],
        capture_output=True, text=True, cwd=str(VISUALIZERS.parent),
    )
    assert proc.returncode == 0, proc.stderr
    assert _last_json(proc.stdout)["status"] == "success"
    assert os.path.exists(out_svg)


def test_visualizer_cli_rejects_malformed_data_json(tmp_path):
    for script, out_name in (
        ("climate_chart.py", "cli_bad_climate.svg"),
        ("population_pyramid.py", "cli_bad_pyramid.svg"),
    ):
        proc = subprocess.run(
            [sys.executable, str(VISUALIZERS / script), "--output", str(tmp_path / out_name),
             "--data-json", "{tidak-valid"],
            capture_output=True, text=True, cwd=str(VISUALIZERS.parent),
        )
        assert proc.returncode == 0, proc.stderr
        result = _last_json(proc.stdout)
        assert result["status"] == "error"
        assert not os.path.exists(tmp_path / out_name)
