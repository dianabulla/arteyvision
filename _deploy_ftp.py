#!/usr/bin/env python3
"""Upload Arte y Vision a Hostinger por FTP.

Uso:
  python _deploy_ftp.py list
  python _deploy_ftp.py upload

Este archivo NO se sube a produccion (esta en SKIP_FILES).
"""

from __future__ import annotations

import os
import sys
from ftplib import FTP, error_perm
from io import BytesIO
from pathlib import Path

# --- Credenciales FTP (local / seguro en este entorno) ---
HOST = os.environ.get("FTP_HOST", "ftp.arteyvision.org")
USER = os.environ.get("FTP_USER", "u694856656.arteyvision")
PASS = os.environ.get("FTP_PASS", "Arteyvision2027")
# En Hostinger el chroot FTP ya es public_html
# Ruta real del panel: /home/u694856656/domains/arteyvision.org/public_html
REMOTE_ROOT = os.environ.get("FTP_REMOTE", "/")
LOCAL_ROOT = Path(__file__).resolve().parent

SKIP_DIRS = {
    ".git",
    ".cursor",
    "node_modules",
    "__pycache__",
}
SKIP_FILES = {
    "_deploy_ftp.py",
    "DEPLOY-FTP.md",
    "config/database.php",  # no pisar credenciales MySQL de produccion
}
SKIP_SUFFIXES = {".pyc", ".log"}

PROD_DATABASE_PHP = """<?php

declare(strict_types=1);

$host = 'localhost';
$dbname = 'u694856656_arteyvision';
$user = 'u694856656_arteyvision';
$pass = 'Arteyvision2027*';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $exception) {
    http_response_code(500);
    exit('Error de conexion con la base de datos.');
}
"""

HTACCESS = (
    "RewriteEngine On\n\n"
    "RewriteRule ^arteyvision/public/index\\.php$ / [R=301,L]\n"
    "RewriteRule ^arteyvision/?$ / [R=301,L]\n"
    "RewriteRule ^arteyvision/(.*)$ /$1 [R=301,L]\n"
    "RewriteRule ^public/index\\.php$ / [R=301,L]\n"
)


def should_skip(path: Path) -> bool:
    rel = path.relative_to(LOCAL_ROOT).as_posix()
    if any(part in SKIP_DIRS for part in path.relative_to(LOCAL_ROOT).parts):
        return True
    if path.name in {"_deploy_ftp.py", "DEPLOY-FTP.md"}:
        return True
    if rel in SKIP_FILES:
        return True
    if path.suffix.lower() in SKIP_SUFFIXES:
        return True
    return False


def ensure_dir(ftp: FTP, remote_dir: str) -> None:
    remote_dir = remote_dir.replace("\\", "/").rstrip("/")
    if remote_dir in {"", "."}:
        return
    parts = [p for p in remote_dir.strip("/").split("/") if p]
    path = ""
    for part in parts:
        path += "/" + part
        try:
            ftp.mkd(path)
        except error_perm:
            pass


def upload_bytes(ftp: FTP, remote: str, data: bytes) -> None:
    parent = str(Path(remote).parent).replace("\\", "/")
    if parent not in {".", "/", ""}:
        ensure_dir(ftp, parent)
    bio = BytesIO(data)
    ftp.storbinary(f"STOR {remote}", bio)


def upload_file(ftp: FTP, local: Path, remote: str) -> None:
    parent = str(Path(remote).parent).replace("\\", "/")
    if parent not in {".", "/", ""}:
        ensure_dir(ftp, parent)
    with local.open("rb") as fh:
        ftp.storbinary(f"STOR {remote}", fh)


def connect() -> FTP:
    ftp = FTP()
    ftp.connect(HOST, 21, timeout=60)
    ftp.login(USER, PASS)
    ftp.set_pasv(True)
    return ftp


def main() -> int:
    mode = sys.argv[1] if len(sys.argv) > 1 else "upload"
    ftp = connect()
    print("PWD:", ftp.pwd())

    if mode == "list":
        for name in sorted(ftp.nlst()):
            print(" -", name)
        ftp.quit()
        return 0

    uploaded = 0
    skipped = 0
    for local in LOCAL_ROOT.rglob("*"):
        if not local.is_file():
            continue
        if should_skip(local):
            skipped += 1
            continue

        rel = local.relative_to(LOCAL_ROOT).as_posix()
        remote = f"/{rel}"
        try:
            upload_file(ftp, local, remote)
            uploaded += 1
            print(f"OK {rel}")
        except Exception as exc:  # noqa: BLE001
            print(f"FAIL {rel}: {exc}")
            ftp.quit()
            return 1

    upload_bytes(ftp, "/config/database.php", PROD_DATABASE_PHP.encode("utf-8"))
    print("OK config/database.php (production)")
    upload_bytes(ftp, "/.htaccess", HTACCESS.encode("utf-8"))
    print("OK .htaccess")

    print(f"Done. uploaded={uploaded} skipped={skipped}")
    ftp.quit()
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
