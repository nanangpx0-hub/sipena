"""SI-PENA E2E part 3/4: suites 4-5."""
import re
from e2e_part1 import get, req, csrf, snap, check
def suite4():
    s, h, b, u = get("/approval")
    html = b.decode("utf-8", "ignore")
    check("S4.approval_http200", s == 200, "GET /approval -> %s" % s)
    check("S4.badges", any(x in html for x in ["DATA_INGESTED", "IN_EDITORIAL", "PENDING_APPROVAL", "APPROVED_LOCKED", "PENDING_DATA"]), "badges")
    m = re.search(rb'/approval/(\d+)', b)
    aid = m.group(1).decode() if m else None
    check("S4.target", bool(aid), "id=%s" % aid)
    if not aid:
        return
    s2, _, b2, _ = get("/approval/%s" % aid)
    h2 = b2.decode("utf-8", "ignore")
    check("S4.approve_btn", (s2 == 200 and ("Approve" in h2) and ("/approval/%s/approve" % aid in h2)), "Approve & Lock")
    check("S4.reject_modal", (s2 == 200 and "remarks" in h2), "Reject modal")
    token = csrf(b2)
    if token:
        s3, h3, b3, _ = req("POST", "/approval/%s/approve" % aid, data={"_token": token}, timeout=60)
        check("S4.approve_go", s3 in (200, 302), "POST approve -> %s" % s3)
        s4, _, b4, _ = get("/approval/%s" % aid)
        check("S4.locked", "APPROVED_LOCKED" in b4.decode("utf-8", "ignore"), "APPROVED_LOCKED")
        s5, _, b5, _ = get("/approval")
        t5 = csrf(b5)
        if t5:
            s6, _, _, _ = req("POST", "/approval/%s/reject" % aid, data={"_token": t5, "remarks": "E2E QA: verifikasi ulang angka Bab 4."}, timeout=60)
            check("S4.reject_go", s6 in (200, 302), "POST reject -> %s" % s6)
            s7, _, b7, _ = get("/approval/%s" % aid)
            check("S4.revision", "IN_EDITORIAL" in b7.decode("utf-8", "ignore"), "IN_EDITORIAL")
        s8, _, b8, _ = get("/approval/%s" % aid)
        t8 = csrf(b8)
        if t8:
            req("POST", "/approval/%s/approve" % aid, data={"_token": t8}, timeout=60)
def suite5():
    s, h, b, u = get("/compilation")
    html = b.decode("utf-8", "ignore")
    check("S5.compilation_http200", s == 200, "GET /compilation -> %s" % s)
    check("S5.lists", ("KDA" in html and "DDA" in html and "SKD" in html), "KDA+DDA+SKD")
    m = re.search(rb'compilation/(\d+)/compile', b)
    cid = m.group(1).decode() if m else None
    check("S5.compile_btn", bool(cid), "id=%s" % cid)
    if not cid:
        return
    token = csrf(b)
    if token:
        s2, h2, b2, _ = req("POST", "/compilation/%s/compile" % cid, data={"_token": token, "sync": "1"}, timeout=180)
        check("S5.typst_go", s2 in (200, 302), "POST compile sync -> %s" % s2)
        s3, h3, b3, _ = get("/compilation/%s/download" % cid, timeout=120)
        ctype = h3.get("Content-Type", "")
        okpdf = (s3 == 200 and (b3[:5] == b"%PDF-" or "pdf" in ctype.lower()) and len(b3) > 50*1024)
        check("S5.pdf_valid", okpdf, "dl %s %.1fKB %s" % (s3, len(b3)/1024, b3[:5]))
        if not okpdf:
            snap("suite5_compilation", b3 if s3 == 200 else b2, "pdf check")
    s4, _, b4, _ = get("/compilation")
    t4 = csrf(b4)
    if t4:
        s5, _, _, _ = req("POST", "/compilation/batch-all-kda", data={"_token": t4}, timeout=120)
        check("S5.batch31", s5 in (200, 302), "POST batch-all -> %s" % s5)
        s6, _, b6, _ = get("/compilation")
        import re as _re
        mq = _re.search(r'Antrean Queue Aktif.*?(\d+)\s*Job', b6.decode("utf-8", "ignore"), _re.S)
        check("S5.jobs", True, ("jobs=" + mq.group(1)) if mq else "dispatched")
