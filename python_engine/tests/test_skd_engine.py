"""Pengujian engine SKD: mode ketat, IKK/IPAK, dan penolakan angka buatan."""
import json
import pathlib
import subprocess
import sys

import pandas as pd
import pytest

import skd_engine as skd

PY = sys.executable
ENGINE = str(pathlib.Path(skd.__file__).resolve())


def test_missing_columns_return_error_without_scores():
    df = pd.DataFrame({"U1_X": [3, 4], "U1_Y": [3, 4]})
    result = skd.calculate_skd_metrics(df, output_svg_path=None)

    assert result["status"] == "error"
    assert "missing_columns" in result
    assert "ikk_score" not in result
    assert any("U2_X" in c for c in result["missing_columns"])


def test_sample_dataset_calculates_ikk_and_ipak():
    df = skd.generate_sample_vkd()
    result = skd.calculate_skd_metrics(df, output_svg_path=None)

    assert result["status"] == "success"
    assert 0 < result["ikk_score"] <= 100
    assert 0 < result["ipak_score"] <= 100
    assert result["mutu_pelayanan"].startswith(("A", "B", "C", "D"))
    assert len(result["attributes"]) == 12
    assert set(result["quadrants"]) == {"A", "B", "C", "D"}


def test_non_numeric_column_is_reported_as_missing():
    df = skd.generate_sample_vkd().drop(columns=["U7_X"])
    result = skd.calculate_skd_metrics(df, output_svg_path=None)
    assert result["status"] == "error"
    assert any("U7_X" in c for c in result["missing_columns"])


def run_engine(*args):
    proc = subprocess.run(
        [PY, ENGINE, *args],
        capture_output=True, text=True, encoding="utf-8", timeout=180,
    )
    assert proc.returncode == 0, proc.stderr
    return json.loads(proc.stdout)


def test_cli_without_file_and_sample_refuses_to_guess(tmp_path):
    payload = run_engine("--output-svg", str(tmp_path / "out.svg"))
    assert payload["status"] == "error"
    assert "ikk_score" not in payload
    assert not (tmp_path / "out.svg").exists()


def test_cli_sample_flag_is_explicit(tmp_path):
    payload = run_engine("--sample", "--output-svg", str(tmp_path / "sample.svg"))
    assert payload["status"] == "success"
    assert payload["data_source"] == "sample"


def test_cli_real_file_reports_file_source(tmp_path):
    df = skd.generate_sample_vkd()
    src = tmp_path / "vkd.xlsx"
    df.to_excel(src, index=False)

    out = tmp_path / "real.svg"
    payload = run_engine("--file", str(src), "--output-svg", str(out))
    assert payload["status"] == "success"
    assert payload["data_source"] == "file"
    assert out.exists()
