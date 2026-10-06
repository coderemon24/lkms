#!/usr/bin/env bash
set -e

echo "================================================================"
echo "  Compiling LKMS Client Package with ionCube Encoder (Linux)"
echo "================================================================"

PACKAGE_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SRC_DIR="${PACKAGE_ROOT}/src"
OUT_DIR="${PACKAGE_ROOT}/dist/encoded-src"

mkdir -p "${OUT_DIR}"

echo "Compiling ${SRC_DIR} into ${OUT_DIR}..."
ioncube_encoder --into "${OUT_DIR}" "${SRC_DIR}" --optimize max --replace-target --obfuscate-variables --obfuscate-properties

echo "[SUCCESS] ionCube compilation completed!"
echo "Replace the content of src/ with dist/encoded-src/ before distributing to clients."
