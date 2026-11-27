#!/usr/bin/env bash
# Inicializa WordPress local en el puerto 8080 (idempotente):
# - Crea .env desde .env.example si no existe
# - Levanta MySQL + WordPress en Docker
# - Instala WordPress en español, activa el tema cvg-alcasa, crea las 6 páginas y permalinks

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WP_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${WP_DIR}"

if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
    COMPOSE_CMD=(docker compose)
elif command -v docker-compose >/dev/null 2>&1; then
    COMPOSE_CMD=(docker-compose)
else
    echo "ERROR: No se encontró 'docker compose' ni 'docker-compose' instalado." >&2
    exit 1
fi

if [ ! -f ".env" ]; then
    cp .env.example .env
    echo "Creado wordpress/.env desde .env.example (revisa y cambia contraseñas si es necesario)."
fi

# Cargar variables de entorno de .env
set -a
# shellcheck disable=SC1091
source .env
set +a

WP_URL="${WP_HOME:-http://localhost:8080}"
WP_USER="${WP_ADMIN_USER:-alcasa_admin}"
WP_PASS="${WP_ADMIN_PASSWORD:-AlcasaAdmin#2026_Cambia}"
WP_EMAIL="${WP_ADMIN_EMAIL:-sistemas@alcasa.com.ve}"

mkdir -p wp-content/plugins/cvg-alcasa-core

echo "1/5 Levantando contenedores WordPress + MySQL en ${WP_URL}..."
"${COMPOSE_CMD[@]}" up -d db wordpress

wp_cli() {
    "${COMPOSE_CMD[@]}" --profile tools run --rm wpcli "$@"
}

echo "2/5 Esperando a que WordPress y MySQL estén disponibles..."
for i in $(seq 1 30); do
    if wp_cli db check >/dev/null 2>&1; then
        break
    fi
    sleep 2
    if [ "$i" -eq 30 ]; then
        echo "ERROR: Tiempo de espera agotado esperando la base de datos MySQL." >&2
        exit 1
    fi
done

echo "3/5 Instalando o verificando instalación base de WordPress..."
if ! wp_cli core is-installed >/dev/null 2>&1; then
    wp_cli core install \
        --url="${WP_URL}" \
        --title="CVG Aluminio del Caroní, S.A. (CVG Alcasa)" \
        --admin_user="${WP_USER}" \
        --admin_password="${WP_PASS}" \
        --admin_email="${WP_EMAIL}" \
        --skip-email
fi

wp_cli language core install es_ES >/dev/null 2>&1 || true
wp_cli site switch-language es_ES >/dev/null 2>&1 || true
wp_cli option update timezone_string "America/Caracas"
wp_cli option update blogdescription "Portal Oficial Institucional — Industria Básica del Aluminio en Venezuela"

echo "4/5 Activando tema cvg-alcasa y creando páginas institucionales..."
wp_cli theme activate cvg-alcasa

ensure_page() {
    local slug="$1"
    local title="$2"
    local template="$3"
    local page_id

    page_id="$(wp_cli post list --post_type=page --name="${slug}" --field=ID --format=ids | awk '{print $1}')"
    if [ -z "${page_id}" ]; then
        page_id="$(wp_cli post create --post_type=page --post_status=publish --post_name="${slug}" --post_title="${title}" --porcelain)"
        echo "  + Página creada: /${slug}/ (ID ${page_id})"
    else
        echo "  = Página existente: /${slug}/ (ID ${page_id})"
    fi

    if [ -n "${template}" ]; then
        wp_cli post meta update "${page_id}" _wp_page_template "${template}" >/dev/null
    fi

    echo "${page_id}"
}

INICIO_ID="$(ensure_page "inicio" "Inicio" "" | tail -n 1)"
ensure_page "productos" "Catálogo de Productos 2025" "page-productos.php" >/dev/null
ensure_page "galeria" "Galería Multimedia e Industrial" "page-galeria.php" >/dev/null
ensure_page "prensa" "Pulso Industrial CVG Alcasa" "page-prensa.php" >/dev/null
ensure_page "periodico" "Periódico Digital CVG Alcasa" "page-periodico.php" >/dev/null
ensure_page "cumpleaneros" "Nuestros Cumpleañeros" "page-cumpleaneros.php" >/dev/null

wp_cli option update show_on_front page
wp_cli option update page_on_front "${INICIO_ID}"

# Activar plugin funcional automáticamente si ya está presente (Fase B)
if [ -f "wp-content/plugins/cvg-alcasa-core/cvg-alcasa-core.php" ]; then
    wp_cli plugin activate cvg-alcasa-core
fi

echo "5/5 Configurando enlaces permanentes (/%postname%/)..."
wp_cli rewrite structure "/%postname%/" --hard
wp_cli rewrite flush --hard

echo ""
echo "Listo: Portal WordPress CVG Alcasa disponible en ${WP_URL}"
echo "Panel wp-admin: ${WP_URL}/wp-admin/ (usuario: ${WP_USER})"
