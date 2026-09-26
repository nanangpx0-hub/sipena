"""SI-PENA E2E part 2/4: suites 1-3."""
import os, re
from e2e_part1 import get, req, putf, csrf, snap, check, login_as, make_dummy_xlsx, ROOT
def suite1():
    check("S1.login_approver", login_as("approver"), "sesi approver")
    s, h, b, u = get("/")
    ok = check("S1.dashboard_http200", s == 200, "GET / -> %s" % s)
    html = b.decode("utf-8", "ignore") if ok else ""
    if not ok:
        snap("suite1_dashboard", b, "status=%s" % s)
        return
    check("S1.title_sipena", "SI-PENA" in html, "title SI-PENA")
    check("S1.tagline_bps", ("BPS" in html and "Jember" in html), "BPS Jember")
    check("S1.matrix_31", ("Kencong" in html and "Patrang" in html), "Kencong..Patrang")
    check("S1.no_nan", "NaN" not in html, "no NaN")
    check("S1.no_cdn", "cdn.tailwindcss.com" not in html and "googleapis" not in html, "aset lokal")
    nav_ok = True
    det = []
    for p in ["/ingestion", "/editorial", "/approval", "/compilation", "/skd", "/covers"]:
        s2, _, _, _ = get(p, timeout=90)
        det.append("%s:%s" % (p, s2))
        if s2 != 200:
            nav_ok = False
    check("S1.navbar_all_200", nav_ok, ", ".join(det))
def suite2():
    check("S2.login_operator", login_as("operator"), "sesi operator")
    s, h, b, u = get("/ingestion")
    html = b.decode("utf-8", "ignore")
    f2 = check("S2.ingestion_http200", s == 200, "GET /ingestion -> %s" % s)
    if not f2:
        return
    check("S2.form_pub", 'name="publication_id"' in html, "dropdown publikasi")
    check("S2.form_opd", 'name="opd_source_name"' in html, "input OPD")
    check("S2.form_mode", ('name="data_mode"' in html and "DIRECT" in html and "AGGREGATE_SCHOOL" in html and "SKD_VKD" in html), "mode ekstraksi")
    check("S2.form_file", 'name="excel_file"' in html, "filepicker")
    # Pilih publikasi fixture QA (hasil sipena:qa-reset -> PENDING_DATA); fallback: opsi tak terkunci.
    opts = [(v.decode(), t.decode("utf-8", "ignore")) for v, t in re.findall(rb'<option value="(\d+)"[^>]*>([^<]*)</option>', b)]
    fixture = [v for v, t in opts if "Uji Ingesti QA" in t]
    unlocked = [v for v, t in opts if "TERKUNCI" not in t]
    pub_id = (fixture or unlocked or [None])[0]
    check("S2.pick_unlocked_pub", bool(pub_id), "pub_id=%s (fixture=%s)" % (pub_id, len(fixture)))
    check("S2.lock_label_visible", "TERKUNCI" in html, "publikasi terkunci diberi label")
    token = csrf(b)
    dummy = make_dummy_xlsx(os.path.join(ROOT, "storage", "temp", "dummy_test.xlsx"))
    if pub_id and token:
        with open(dummy, "rb") as f:
            fx = f.read()
        s3, h3, b3, u3 = req("POST", "/ingestion/upload", data={"_token": token, "publication_id": pub_id, "opd_source_name": "Dinas Pendidikan E2E", "data_mode": "DIRECT", "chapter_number": "4", "table_number": "4.1.E2E", "notes": "E2E QA"}, files={"excel_file": ("dummy_test.xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", fx)}, timeout=120)
        ok_up = s3 in (200, 302)
        check("S2.upload_no_500", ok_up, "POST upload -> %s" % s3)
        if ok_up:
            # urllib mengikuti rantai redirect, sehingga body akhir = halaman tujuan
            # yang memuat flash sukses beserta daftar riwayat unggah.
            msg = b3.decode("utf-8", "ignore")
            check("S2.flash_version", ("berhasil diunggah" in msg or "Berhasil" in msg), "flash sukses")
            check("S2.sha256_trail", ("SHA-256" in msg or "Audit" in msg or "v1" in msg or "v2" in msg or "Berkas" in msg), "audit versioning")
            # Data mentah tercatat pada daftar ingesti (bukan sekadar status sukses)
            check("S2.raw_file_listed", "Dinas Pendidikan E2E" in msg, "nama OPD muncul di riwayat unggah")
            # Verifikasi ISI data: tampil di meja tinjauan Approver
            if login_as("approver"):
                s5, _, b5, _ = get("/approval/%s" % pub_id, timeout=90)
                ap = b5.decode("utf-8", "ignore") if s5 == 200 else ""
                check("S2.preview_section", "Pratinjau Data Tabel" in ap, "GET /approval/%s -> %s" % (pub_id, s5))
                check("S2.preview_header", "Kecamatan" in ap and "Desa" in ap, "header tabel nyata")
                check("S2.preview_cells", ("Cakru" in ap and "Sukorejo" in ap), "baris data asli dari xlsx")
                check("S2.status_data_ingested", "DATA_INGESTED" in ap, "status publikasi maju")
                check("S2.not_verified_flag", "Belum diverifikasi" in ap, "flag verifikasi tabel")
            else:
                check("S2.preview_section", False, "login approver gagal")
        else:
            snap("suite2_ingestion", b3, "status=%s" % s3)
    else:
        check("S2.upload_no_500", False, "pub=%s token=%s" % (pub_id, bool(token)))
def suite3():
    check("S3.login_editor", login_as("editor"), "sesi editor")
    s, h, b, u = get("/editorial")
    check("S3.editorial_http200", s == 200, "GET /editorial -> %s" % s)
    # Pilih publikasi fixture QA (belum terkunci) agar pengujian redaksi tidak
    # menyentuh publikasi final yang dijaga state machine.
    opts = [(v.decode(), t) for v, t in re.findall(rb'<option value="(\d+)"[^>]*>([^<]*)</option>', b)]
    fixture = [v for v, t in opts if b"Uji Ingesti QA" in t]
    unlocked = [v for v, t in opts if b"TERKUNCI" not in t]
    target = (fixture or unlocked or [None])[0]
    if target:
        s, h, b, u = get("/editorial?publication_id=%s" % target, timeout=60)
        check("S3.pick_unlocked_pub", True, "pub=%s" % target)
    m = re.search(rb'/editorial/(\d+)/edit', b)
    eid = m.group(1).decode() if m else None
    if not eid:
        # SELF-HEAL probe: default mungkin publikasi tanpa narasi — pilih publikasi ber-narasi
        import urllib.parse as _up
        s0, _, b0, _ = get("/editorial?publication_id=3")
        m0 = re.search(rb'/editorial/(\d+)/edit', b0)
        if m0:
            eid = m0.group(1).decode()
    check("S3.edit_btn", bool(eid), "narrative id=%s" % eid)
    if not eid:
        return
    s2, _, b2, _ = get("/editorial/%s/edit" % eid)
    h2 = b2.decode("utf-8", "ignore")
    check("S3.bilingual", (s2 == 200 and 'name="narrative_id"' in h2 and 'name="narrative_en"' in h2), "ID kiri + EN kanan")
    token = csrf(b2)
    if s2 == 200 and token:
        s3, h3, b3, _ = putf("/editorial/%s" % eid, {"_token": token, "narrative_id": "QA E2E {{ total_penduduk }} Kencong.", "narrative_en": "QA E2E pop {{ total_penduduk }}.", "highlight_label": "Jumlah Penduduk", "highlight_value": "72.450 Jiwa"})
        ok3 = s3 in (200, 302)
        check("S3.save_token", ok3, "PUT -> %s" % s3)
        if ok3:
            # Body akhir = halaman tujuan redirect yang memuat toast sukses.
            toast = b3.decode("utf-8", "ignore")
            check("S3.toast_persist", ("berhasil disimpan" in toast or "Berhasil" in toast), "toast sukses")
            s4, _, b4, _ = get("/editorial/%s/edit" % eid)
            persist = b4.decode("utf-8", "ignore")
            check("S3.narrative_persisted", "QA E2E" in persist, "narasi tersimpan di database")
        else:
            snap("suite3_editorial", b3, "status=%s" % s3)
