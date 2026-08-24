#!/usr/bin/env python3
"""Static invariants for the no-Composer Hostinger deployment artifact."""

from __future__ import annotations

import base64
import gzip
import hashlib
import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MARKER = "2026-08-24-hostinger-launch-v2"
failures: list[str] = []


def check(condition: bool, message: str) -> None:
    print(f"  {'PASS' if condition else 'FAIL'}  {message}")
    if not condition:
        failures.append(message)


def main() -> int:
    check(not (ROOT / "composer.json").exists(), "root composer.json is absent")
    check(not (ROOT / "composer.lock").exists(), "root composer.lock is absent")
    check((ROOT / ".composer-backup/composer.json").is_file(), "development manifest is backed up")

    backup = ROOT / "bootstrap/autoload_backup"
    expected: dict[str, str] = {}
    for path in sorted(backup.glob("*.php")):
        expected[path.name] = path.read_text(encoding="utf-8")
        live = ROOT / "vendor" / ("autoload.php" if path.name == "autoload.php" else f"composer/{path.name}")
        check(live.is_file(), f"vendored {path.name} exists")
        if live.is_file():
            check(path.read_bytes() == live.read_bytes(), f"backup {path.name} matches vendor")
    check(len(expected) == 11, "autoload backup has exactly 11 recovery files")

    doctor = (ROOT / "doctor.php").read_text(encoding="utf-8")
    payload_match = re.search(
        r"const DOCTOR_BUNDLE_BASE64 = <<<'HUVANTI_AUTOLOAD_BUNDLE'\n(.*?)\nHUVANTI_AUTOLOAD_BUNDLE;",
        doctor,
        re.S,
    )
    hash_match = re.search(r"const DOCTOR_BUNDLE_SHA256 = '([a-f0-9]{64})';", doctor)
    check(payload_match is not None and hash_match is not None, "doctor contains embedded payload and checksum")
    if payload_match and hash_match:
        encoded = "".join(payload_match.group(1).splitlines())
        compressed = base64.b64decode(encoded, validate=True)
        check(hashlib.sha256(compressed).hexdigest() == hash_match.group(1), "doctor payload checksum is valid")
        bundled = json.loads(gzip.decompress(compressed))
        check(bundled == expected, "doctor payload exactly matches pristine backup")

    files_with_marker = [
        ".htaccess",
        "public/.htaccess",
        "public/deployment.json",
        "public/index.php",
        "install.php",
        "doctor.php",
        "deploy.sh",
        "HOSTINGER_DEPLOYMENT.md",
    ]
    for name in files_with_marker:
        check(MARKER in (ROOT / name).read_text(encoding="utf-8"), f"deployment marker synchronized in {name}")

    root_htaccess = (ROOT / ".htaccess").read_text(encoding="utf-8")
    public_guard = root_htaccess.find("RewriteRule ^public")
    public_rewrite = root_htaccess.find("RewriteRule ^(.*)$ public/$1")
    check(public_guard >= 0 and public_rewrite > public_guard, "root rewrite guards already-public paths")
    check("%{REQUEST_FILENAME} -f" not in root_htaccess, "root rewrite does not expose existing private files")

    deployment = json.loads((ROOT / "public/deployment.json").read_text(encoding="utf-8"))
    check(deployment.get("deployment") == MARKER, "public deployment JSON reports current marker")

    forbidden_refs = []
    for name in ["README.md", "HOSTINGER_DEPLOYMENT.md", "bootstrap/autoload_backup/README.md", "public/index.php"]:
        if "install.php?repair=1" in (ROOT / name).read_text(encoding="utf-8"):
            forbidden_refs.append(name)
    check(not forbidden_refs, "documentation and front controller no longer expose installer repair actions")

    if failures:
        print(f"\n{len(failures)} deployment invariant(s) failed.", file=sys.stderr)
        return 1
    print("\nAll deployment invariants passed.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
