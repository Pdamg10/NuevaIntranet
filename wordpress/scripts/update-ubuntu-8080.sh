#!/usr/bin/env bash
# Actualización en UN SOLO COMANDO en Ubuntu (:8080) tras subir cambios a GitHub.
# Conserva intactos MySQL, usuarios, contactos, postulaciones y CVs privados.
# Uso:
#   ./wordpress/scripts/update-ubuntu-8080.sh
#   ./wordpress/scripts/update-ubuntu-8080.sh --branch copia

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WP_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
REPO_DIR="$(cd "${WP_DIR}/.." && pwd)"
BRANCH="copia"

while [ "$#" -gt 0 ]; do
    case "$1" in
        --branch)
            BRANCH="${2:-copia}"
            shift 2
            ;;
        -h|--help)
            echo "Uso: $0 [--branch copia]"
            exit 0
            ;;
        *)
            echo "Opción no reconocida: $1" >&2
            exit 1
            ;;
    esac
done

if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
    COMPOSE_CMD=(docker compose)
elif command -v docker-compose >/dev/null 2>&1; then
    COMPOSE_CMD=(docker-compose)
else
    echo "ERROR: No se encontró Docker Compose." >&2
    exit 1
fi

echo "1/4 Descargando últimos cambios de GitHub (rama ${BRANCH})..."
cd "${REPO_DIR}"
git fetch origin "${BRANCH}"
git pull --ff-only origin "${BRANCH}"

echo "2/4 Asegurando contenedores activos en puerto 8080..."
cd "${WP_DIR}"
"${COMPOSE_CMD[@]}" up -d db wordpress

wp_cli() {
    "${COMPOSE_CMD[@]}" --profile tools run --rm wpcli "$@"
}

echo "3/4 Refrescando tema, plugin, cachés y enlaces permanentes..."
wp_cli theme activate cvg-alcasa >/dev/null 2>&1 || true
if [ -f "wp-content/plugins/cvg-alcasa-core/cvg-alcasa-core.php" ]; then
    wp_cli plugin activate cvg-alcasa-core >/dev/null 2>&1 || true
fi
wp_cli transient delete --all >/dev/null 2>&1 || true
wp_cli cache flush >/dev/null 2>&1 || true
wp_cli rewrite flush --hard >/dev/null 2>&1 || true

echo "4/4 Verificando respuesta del portal..."
WP_PORT_VAL="8080"
if [ -f ".env" ]; then
    # shellcheck disable=SC1091
    source .env
    WP_PORT_VAL="${WP_PORT:-8080}"
fi
HTTP_STATUS="$(curl -sS -o /dev/null -w "%{http_code}" --max-time 10 "http://127.0.0.1:${WP_PORT_VAL}/" || echo "000")"
echo "Actualización finalizada — HTTP ${HTTP_STATUS} en http://localhost:${WP_PORT_VAL}/"
