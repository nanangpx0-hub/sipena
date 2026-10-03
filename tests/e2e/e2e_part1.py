#!/usr/bin/env python3
"""SI-PENA E2E QA harness part 1/4: helpers."""
import io, os, re, sys, time, json, zipfile
import urllib.request, urllib.parse, urllib.error, http.cookiejar
from datetime import datetime
BASE = "http://127.0.0.1:8000"
ROOT = r"C:\laragon\www\sipena"
ART = os.path.join(ROOT, "storage", "temp", "e2e_artifacts")
os.makedirs(ART, exist_ok=True)
LOGF = os.path.join(ROOT, "storage", "logs", "laravel.log")
results = []
jar = http.cookiejar.CookieJar()
def req(method, path, data=None, headers=None, files=None, timeout=60):
    url = BASE + path
    heads = dict(headers or {})
    body = None
    if files:
        bnd = "----SipenaE2E%x" % int(time.time()*1000)
        buf = io.BytesIO()
        for k, v in (data or {}).items():
            buf.write(("--%s\r\n" % bnd).encode())
            buf.write(('Content-Disposition: form-data; name="%s"\r\n\r\n%s\r\n' % (k, v)).encode())
        for fk, (fname, ctype, fb) in files.items():
            buf.write(("--%s\r\n" % bnd).encode())
            buf.write(('Content-Disposition: form-data; name="%s"; filename="%s"\r\n' % (fk, fname)).encode())
            buf.write(("Content-Type: %s\r\n\r\n" % ctype).encode())
            buf.write(fb + b"\r\n")
        buf.write(("--%s--\r\n" % bnd).encode())
        body = buf.getvalue()
        heads["Content-Type"] = "multipart/form-data; boundary=" + bnd
    elif data is not None:
        body = urllib.parse.urlencode(data).encode()
        heads.setdefault("Content-Type", "application/x-www-form-urlencoded")
    op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    r = urllib.request.Request(url, data=body, headers=heads, method=method)
    try:
        with op.open(r, timeout=timeout) as resp:
            return resp.status, dict(resp.headers), resp.read(), resp.geturl()
    except urllib.error.HTTPError as e:
        return e.code, dict(e.headers), e.read(), url
def get(path, **kw):
    return req("GET", path, **kw)
def putf(path, fields, timeout=60):
    fields = dict(fields)
    fields["_method"] = "PUT"
    return req("POST", path, data=fields, timeout=timeout)
def csrf(html):
    m = re.search(rb'name="_token"[^>]*value="([^"]+)"', html)
    if m:
        return m.group(1).decode()
    m2 = re.search(rb'csrf-token"\s+content="([^"]+)"', html)
    return m2.group(1).decode() if m2 else None
def snap(name, body, extra=""):
    with open(os.path.join(ART, "failure_%s.html" % name), "wb") as f:
        f.write(body[:200000])
    if os.path.exists(LOGF):
        with open(LOGF, "rb") as f:
            tail = f.read()[-20000:]
        with open(os.path.join(ART, "failure_%s.log" % name), "wb") as f:
            f.write(("--- %s %s %s ---\n" % (datetime.now().isoformat(), name, extra)).encode() + tail)
def check(name, cond, detail=""):
    results.append((name, bool(cond), detail))
    print(("PASS " if cond else "FAIL ") + name + ((" :: " + detail) if detail else ""), flush=True)
    return bool(cond)

ACCOUNTS = {
    "operator": ("operator@bps3509.go.id", "Operator Data"),
    "editor": ("editor@bps3509.go.id", "Editor Bahasa"),
    "approver": ("approver@bps3509.go.id", "Ketua Tim"),
    "viewer": ("viewer@bps3509.go.id", "Pimpinan"),
}
PASSWORD = os.environ.get("SIENA_E2E_PASSWORD", "password123")

def make_vkd_xlsx(path):
    """Berkas kuesioner VKD 12 atribut (U1-U12, kolom _X dan _Y) untuk uji engine SKD."""
    os.makedirs(os.path.dirname(path), exist_ok=True)
    import xml.sax.saxutils as sx
    codes = ["U%d" % i for i in range(1, 13)]
    hdr = [c + "_X" for c in codes] + [c + "_Y" for c in codes]
    def cell(v):
        return '<c t="inlineStr"><is><t>%s</t></is></c>' % sx.escape(str(v))
    def row(vals):
        return "<row>" + "".join(cell(v) for v in vals) + "</row>"
    srows = row(hdr)
    # Sebar skor tiap atribut ke seluruh kuadran A/B/C/D terhadap grand mean
    # (3.5) sehingga tabel matriks 12 unsur benar-benar memuat label Kuadran A-D.
    levels = [(2, 5), (5, 5), (2, 2), (5, 2)]  # (X/kepuasan, Y/kepentingan)
    xs = [levels[i % 4][0] for i in range(12)]
    ys = [levels[i % 4][1] for i in range(12)]
    for _ in range(40):
        srows += row(xs + ys)
    with zipfile.ZipFile(path, "w", zipfile.ZIP_DEFLATED) as z:
        z.writestr("[Content_Types].xml", '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>')
        z.writestr("_rels/.rels", '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>')
        z.writestr("xl/workbook.xml", '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="VKD" sheetId="1" r:id="rId1"/></sheets></workbook>')
        z.writestr("xl/_rels/workbook.xml.rels", '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>')
        z.writestr("xl/worksheets/sheet1.xml", '<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>' + srows + "</sheetData></worksheet>")
    return path

def login_as(role="operator"):
    """Pastikan sesi E2E berada pada peran tertentu (logout -> login -> verifikasi peran)."""
    s0, _, b0, _ = get("/dashboard", timeout=60)
    if s0 == 200:  # sesi lama masih aktif -> keluar dulu
        token0 = csrf(b0)
        if token0:
            req("POST", "/logout", data={"_token": token0}, timeout=60)

    s, _, b, _ = get("/login", timeout=60)
    if s != 200:
        return False
    token = csrf(b)
    email, role_label = ACCOUNTS.get(role, (ACCOUNTS["operator"][0], ACCOUNTS["operator"][1]))
    if not token:
        return False
    req("POST", "/login", data={"_token": token, "email": email, "password": PASSWORD}, timeout=60)

    s2, _, b2, _ = get("/dashboard", timeout=60)
    html = b2.decode("utf-8", "ignore") if s2 == 200 else ""
    return s2 == 200 and role_label in html
def make_dummy_xlsx(path):
    os.makedirs(os.path.dirname(path), exist_ok=True)
    hdrs = ["Kecamatan", "Desa", "Guru", "Murid"]
    rows = [["Kencong", "Cakru", "10", "200"], ["Kencong", "Sukorejo", "12", "250"]]
    import xml.sax.saxutils as sx
    def cell(v):
        return "<c t=\"inlineStr\"><is><t>%s</t></is></c>" % sx.escape(v)
    srows = "<row>" + "".join(cell(h) for h in hdrs) + "</row>"
    for rr in rows:
        srows += "<row>" + "".join(cell(v) for v in rr) + "</row>"
    with zipfile.ZipFile(path, "w", zipfile.ZIP_DEFLATED) as z:
        z.writestr("[Content_Types].xml", '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>')
        z.writestr("_rels/.rels", '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>')
        z.writestr("xl/workbook.xml", '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets></workbook>')
        z.writestr("xl/_rels/workbook.xml.rels", '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>')
        z.writestr("xl/worksheets/sheet1.xml", '<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>' + srows + "</sheetData></worksheet>")
    return path
