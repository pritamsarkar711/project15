#!/usr/bin/env python3
"""Static invariants for the vendored Hostinger deployment artifact.

The packaging (see HOSTINGER_DEPLOYMENT.md) keeps everything deployable on a
host without a manual Composer or npm step:

- root composer.json + composer.lock are tracked so any Composer run on the
  host installs the real dependency list;
- vendor/ is committed as a fallback and bootstrap/autoload_backup/ keeps
  pristine generated loader files for self-repair;
- the deployment marker is synchronized across .htaccess, public/index.php,
  public/deployment.json, deploy.sh and this documentation;
- the old AI Studio React scaffold and other dead files stay removed.

Run from the repository root: python3 tests/deployment_static.py
"""

from __future__ import annotations

import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MARKER = "v70-2026-09-03-faq-survives-approval"
failures: list[str] = []


def check(condition: bool, message: str) -> None:
    print(f"  {'PASS' if condition else 'FAIL'}  {message}")
    if not condition:
        failures.append(message)


def main() -> int:
    # --- Root manifests are tracked and real -----------------------------
    composer = ROOT / "composer.json"
    lock = ROOT / "composer.lock"
    check(composer.is_file(), "root composer.json exists")
    check(lock.is_file(), "root composer.lock exists")
    if composer.is_file():
        data = json.loads(composer.read_text(encoding="utf-8"))
        requires = {**data.get("require", {}), **data.get("require-dev", {})}
        check(
            any(pkg.startswith("laravel/framework") for pkg in requires),
            "composer.json requires laravel/framework (not the old dependency-less stub)",
        )
        psr4 = data.get("autoload", {}).get("psr-4", {})
        check("App\\\\" in psr4 or "App\\" in psr4, "composer.json maps the App\\\\ PSR-4 prefix")

    # --- Backup mirror ----------------------------------------------------
    backup_dir = ROOT / ".composer-backup"
    backup_composer = backup_dir / "composer.json"
    backup_lock = backup_dir / "composer.lock"
    check(backup_composer.is_file(), "development manifest is mirrored under .composer-backup/")
    check(backup_lock.is_file(), "development lock is mirrored under .composer-backup/")
    if lock.is_file() and backup_lock.is_file():
        check(
            lock.read_bytes() == backup_lock.read_bytes(),
            "root composer.lock matches the .composer-backup mirror",
        )

    # --- Pristine autoloader recovery set ---------------------------------
    backup = ROOT / "bootstrap/autoload_backup"
    for path in sorted(backup.glob("*.php")):
        live = ROOT / "vendor" / ("autoload.php" if path.name == "autoload.php" else f"composer/{path.name}")
        check(live.is_file(), f"vendored {path.name} exists")
        if live.is_file():
            check(path.read_bytes() == live.read_bytes(), f"backup {path.name} matches vendor")
    check(len(list(backup.glob("*.php"))) == 11, "autoload backup has exactly 11 recovery files")

    # --- Deployment marker synchronization --------------------------------
    htaccess = (ROOT / ".htaccess").read_text(encoding="utf-8")
    index_php = (ROOT / "public/index.php").read_text(encoding="utf-8")
    deploy_sh = (ROOT / "deploy.sh").read_text(encoding="utf-8")
    doc = (ROOT / "HOSTINGER_DEPLOYMENT.md").read_text(encoding="utf-8")

    header_match = re.search(r'Header always set X-Huvanti-Deploy "([^"]+)"', htaccess)
    define_match = re.search(r"define\('HUVANTI_DEPLOY_VERSION',\s*'([^']+)'\)", index_php)
    deploy_match = re.search(r'readonly DEPLOYMENT="([^"]+)"', deploy_sh)
    doc_marker_block = re.search(
        r"Current deployment marker:\s*\n\n```text\n([^\n]+)\n```", doc
    )

    check(header_match is not None and header_match.group(1) == MARKER, ".htaccess header reports the current marker")
    check(define_match is not None and define_match.group(1) == MARKER, "public/index.php reports the current marker")
    check(deploy_match is not None and deploy_match.group(1) == MARKER, "deploy.sh reports the current marker")
    check(doc_marker_block is not None and doc_marker_block.group(1) == MARKER, "HOSTINGER_DEPLOYMENT.md quotes the current marker")

    deployment_json_path = ROOT / "public/deployment.json"
    check(deployment_json_path.is_file(), "public/deployment.json exists")
    if deployment_json_path.is_file():
        deployment = json.loads(deployment_json_path.read_text(encoding="utf-8"))
        check(deployment.get("deployment") == MARKER, "public deployment JSON reports the current marker")

    markers = {
        "htaccess": header_match.group(1) if header_match else None,
        "index.php": define_match.group(1) if define_match else None,
        "deploy.sh": deploy_match.group(1) if deploy_match else None,
        "deployment.json": deployment.get("deployment") if deployment_json_path.is_file() else None,
    }
    check(
        None not in markers.values() and len(set(markers.values())) == 1,
        "all runtime marker sources are synchronized with each other",
    )

    # --- Root rewrite guards ----------------------------------------------
    public_guard = htaccess.find("RewriteRule ^public")
    public_rewrite = htaccess.find("RewriteRule ^(.*)$ public/$1")
    check(public_guard >= 0 and public_rewrite > public_guard, "root rewrite guards already-public paths")
    check("%{REQUEST_FILENAME} -f" not in htaccess, "root rewrite does not expose existing private files")

    # --- No installer repair action leaks ----------------------------------
    forbidden_refs = []
    for name in ["README.md", "HOSTINGER_DEPLOYMENT.md", "bootstrap/autoload_backup/README.md", "public/index.php"]:
        if "install.php?repair=1" in (ROOT / name).read_text(encoding="utf-8"):
            forbidden_refs.append(name)
    check(not forbidden_refs, "documentation and front controller do not expose installer repair actions")

    # --- Removed junk stays removed -----------------------------------------
    removed = [
        "index.html",
        "tsconfig.json",
        "metadata.json",
        "src/App.tsx",
        "src/main.tsx",
        "src/index.css",
        "public/images/hero-person.png",
        "public/images/logo.svg",
        "public/images/favicon-32.png",
        "public/images/favicon-64.png",
        ".composer-backup/deploy-stub.composer.json",
    ]
    for name in removed:
        check(not (ROOT / name).exists(), f"removed junk stays removed: {name}")

    # --- Compiled frontend assets are committed ------------------------------
    manifest_path = ROOT / "public/build/manifest.json"
    check(manifest_path.is_file(), "public/build/manifest.json exists")
    if manifest_path.is_file():
        manifest = json.loads(manifest_path.read_text(encoding="utf-8"))
        for key in manifest:
            entry = ROOT / "public/build" / manifest[key]["file"]
            check(entry.is_file(), f"manifest entry exists on disk: {manifest[key]['file']}")
        check(
            "resources/css/app.css" in manifest,
            "manifest contains the app.css entry",
        )

    # --- Compiled provider maps remain valid ---------------------------------
    for cache in ["bootstrap/cache/packages.php", "bootstrap/cache/services.php"]:
        path = ROOT / cache
        check(path.is_file() and path.read_text(encoding="utf-8").startswith("<?php"), f"{cache} is a valid PHP cache file")

    # --- Cheap secret scan (tracked files outside vendor/build) --------------
    secret_pattern = re.compile(r"ghp_[A-Za-z0-9]{20,}|gho_[A-Za-z0-9]{20,}|github_pat_[A-Za-z0-9_]{20,}|sk-[A-Za-z0-9]{20,}")
    leaked = []
    for path in ROOT.rglob("*"):
        if not path.is_file():
            continue
        rel = path.relative_to(ROOT).as_posix()
        if rel.startswith(("vendor/", "node_modules/", "public/build/", ".git/")):
            continue
        if path.suffix in {".png", ".jpg", ".jpeg", ".webp", ".ico", ".gif", ".woff", ".woff2", ".ttf"}:
            continue
        try:
            if secret_pattern.search(path.read_text(encoding="utf-8", errors="ignore")):
                leaked.append(rel)
        except OSError:
            continue
    check(not leaked, f"no token-shaped secrets in tracked files{': ' + ', '.join(leaked) if leaked else ''}")

    if failures:
        print(f"\n{len(failures)} deployment invariant(s) failed.", file=sys.stderr)
        return 1
    print("\nAll deployment invariants passed.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
