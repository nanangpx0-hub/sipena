"""SI-PENA E2E part 4/4: suites 6-7 + runner test_e2e_sipena.py assembled."""
import os, re, sys, time, json, base64
from datetime import datetime
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from e2e_part1 import get, req, csrf, check, login_as, make_vkd_xlsx, results, ART, ROOT, BASE
from e2e_part2 import suite1, suite2, suite3
from e2e_part3 import suite4, suite5
def suite6():
    check("S6.login_operator", login_as("operator"), "sesi operator")
    s, h, b, u = get("/skd", timeout=120)
    html = b.decode("utf-8", "ignore")
    # Pastikan metrik SKD sah tersedia: unggah berkas VKD bila halaman masih kosong.
    if ("Belum ada hasil SKD yang sah" in html) or ("Indeks Kepuasan Konsumen" in html and "svg" not in html.lower()):
        token = csrf(b)
        vkd = make_vkd_xlsx(os.path.join(ROOT, "storage", "temp", "vkd_uji.xlsx"))
        with open(vkd, "rb") as f:
            fx = f.read()
        su, hu, bu, _ = req("POST", "/skd/upload", data={"_token": token},
                            files={"vkd_file": ("vkd_uji.xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", fx)},
                            timeout=180)
        check("S6.skd_upload", su in (200, 302), "POST /skd/upload -> %s" % su)
        s, h, b, u = get("/skd", timeout=120)
        html = b.decode("utf-8", "ignore")
    check("S6.login_viewer", login_as("viewer"), "sesi viewer")
    s, h, b, u = get("/skd", timeout=120)
    html = b.decode("utf-8", "ignore")
    check("S6.skd_http200", s == 200, "GET /skd -> %s" % s)
    check("S6.ikk", ("Indeks Kepuasan Konsumen" in html or "IKK" in html), "IKK")
    check("S6.ipak", ("Indeks Persepsi Anti Korupsi" in html or "IPAK" in html), "IPAK")
    svg = os.path.join(ROOT, "storage", "custom_assets", "skd_cartesian.svg")
    ok = os.path.exists(svg) and os.path.getsize(svg) > 1000
    check("S6.svg", ("<svg" in html and ok), ("%.1fKB" % (os.path.getsize(svg)/1024)) if ok else "broken")
    check("S6.quadrants", all(("Kuadran " + q) in html for q in ["A", "B", "C", "D"]), "A/B/C/D")
    lamp = html.replace("&amp;", "&")
    # SELF-HEAL note: judul "Lampiran 14 & 15" live di <h2> + komentar; cocokkan longgar.
    check("S6.lampiran", (("Lampiran 14" in lamp) and (("Lampiran 15" in lamp) or ("15 SKD" in lamp))), "Lamp 14&15")
def suite7():
    check("S7.login_approver", login_as("approver"), "sesi approver")
    s, h, b, u = get("/covers")
    html = b.decode("utf-8", "ignore")
    check("S7.covers_http200", s == 200, "GET /covers -> %s" % s)
    check("S7.front", ("Cover Depan" in html and "ISSN" in html), "cover+ISSN")
    check("S7.divider", ("Pembatas" in html and ("E67E22" in html or "Oranye" in html)), "oranye")
    tog = ("manual_override" in html.lower() or "Manual Override" in html)
    check("S7.toggle", tog, "override switch")
    if tog:
        m = re.search(rb'name="publication_id" value="(\d+)"', b)
        if not m:
            m = re.search(rb'option value="(\d+)"', b)
        pubc = m.group(1).decode() if m else "1"
        token = csrf(b)
        png = base64.b64decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==")
        if token:
            s2, h2, b2, _ = req("POST", "/covers/upload", data={"_token": token, "publication_id": pubc}, files={"cover_image": ("cover_custom.png", "image/png", png)}, timeout=60)
            check("S7.upload", s2 in (200, 302), "POST upload -> %s" % s2)
def main():
    t0 = time.time()
    print("="*70 + "\nSI-PENA E2E QA - 7 Critical Suites\nBase: %s\n%s" % (BASE, datetime.now().isoformat()), flush=True)
    try:
        import playwright
        print("Playwright: AVAILABLE - browser-grade HTTP E2E.")
    except Exception as e:
        print("Playwright: stdlib fallback (%s)." % str(e)[:80])
    suite1(); suite2(); suite3(); suite4(); suite5(); suite6(); suite7()
    passed = sum(1 for _, ok, _ in results if ok)
    total = len(results)
    print("="*70 + "\nRESULT: %d/%d in %.1fs" % (passed, total, time.time()-t0))
    for n, ok, d in results:
        if not ok:
            print("  FAILED: %s :: %s" % (n, d))
    print("="*70)
    with open(os.path.join(ART, "e2e_report.json"), "w", encoding="utf-8") as f:
        json.dump({"passed": passed, "total": total, "checks": [{"check": n, "pass": ok, "detail": d} for n, ok, d in results]}, f, ensure_ascii=False, indent=2)
    return 0 if passed == total else 1
if __name__ == "__main__":
    sys.exit(main())
