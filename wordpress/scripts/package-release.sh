#!/usr/bin/env bash
# Empaqueta artefactos limpios para instalación o actualización en WordPress / Azure App Service:
#   - wordpress/dist/cvg-alcasa-theme.zip         (solo tema cvg-alcasa)
#   - wordpress/dist/cvg-alcasa-core-plugin.zip   (solo plugin cvg-alcasa-core)
#   - wordpress/dist/cvg-alcasa-azure-bundle.zip  (wp-content tema+plugin + alcasa-data sin datos personales)

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WP_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
REPO_DIR="$(cd "${WP_DIR}/.." && pwd)"
DIST_DIR="${WP_DIR}/dist"
STAGE_DIR="$(mktemp -d)"

cleanup() {
    rm -rf "${STAGE_DIR}"
}
trap cleanup EXIT

mkdir -p "${DIST_DIR}"

if ! command -v zip >/dev/null 2>&1 && ! command -v python3 >/dev/null 2>&1; then
    echo "ERROR: Se requiere 'zip' o 'python3' para empaquetar los artefactos." >&2
    exit 1
fi

make_zip() {
    local src_dir="$1"
    local out_zip="$2"
    rm -f "${out_zip}"
    if command -v zip >/dev/null 2>&1; then
        (cd "${src_dir}" && zip -qr "${out_zip}" .)
    else
        python3 - "$src_dir" "$out_zip" <<'PY'
import os, sys, zipfile
src, out = sys.argv[1], sys.argv[2]
with zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as zf:
    for root, _, files in os.walk(src):
        for fn in sorted(files):
            full = os.path.join(root, fn)
            zf.write(full, os.path.relpath(full, src))
PY
    fi
}

echo "1/3 Empaquetando tema cvg-alcasa..."
mkdir -p "${STAGE_DIR}/theme"
cp -R "${WP_DIR}/wp-content/themes/cvg-alcasa" "${STAGE_DIR}/theme/cvg-alcasa"
make_zip "${STAGE_DIR}/theme" "${DIST_DIR}/cvg-alcasa-theme.zip"

echo "2/3 Empaquetando plugin cvg-alcasa-core..."
mkdir -p "${STAGE_DIR}/plugin"
cp -R "${WP_DIR}/wp-content/plugins/cvg-alcasa-core" "${STAGE_DIR}/plugin/cvg-alcasa-core"
make_zip "${STAGE_DIR}/plugin" "${DIST_DIR}/cvg-alcasa-core-plugin.zip"

echo "3/3 Empaquetando bundle completo para Azure App Service..."
BUNDLE="${STAGE_DIR}/bundle"
mkdir -p "${BUNDLE}/wp-content/themes" "${BUNDLE}/wp-content/plugins" "${BUNDLE}/alcasa-data"
cp -R "${WP_DIR}/wp-content/themes/cvg-alcasa" "${BUNDLE}/wp-content/themes/cvg-alcasa"
cp -R "${WP_DIR}/wp-content/plugins/cvg-alcasa-core" "${BUNDLE}/wp-content/plugins/cvg-alcasa-core"

# Copiar solo archivos de datos públicos/semilla (NUNCA datos personales como cumpleaneros.json ni storage/)
for f in contenido.json noticias.json contactos-semilla.csv postulaciones-semilla.csv; do
    if [ -f "${REPO_DIR}/data/${f}" ]; then
        cp "${REPO_DIR}/data/${f}" "${BUNDLE}/alcasa-data/${f}"
    fi
done

make_zip "${BUNDLE}" "${DIST_DIR}/cvg-alcasa-azure-bundle.zip"

echo ""
echo "Paquetes generados en ${DIST_DIR}:"
ls -lh "${DIST_DIR}"/*.zip
