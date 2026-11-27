#!/usr/bin/env bash
# Instalación en UN SOLO COMANDO para Ubuntu en el puerto 8080 (idempotente y segura).
# Uso:
#   ./wordpress/scripts/install-ubuntu-8080.sh
#   ./wordpress/scripts/install-ubuntu-8080.sh --url http://IP_SERVIDOR:8080 --open-firewall

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WP_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${WP_DIR}"

PUBLIC_URL=""
OPEN_FIREWALL=false

while [ "$#" -gt 0 ]; do
    case "$1" in
        --url)
            PUBLIC_URL="${2:-}"
            shift 2
            ;;
        --open-firewall)
            OPEN_FIREWALL=true
            shift
            ;;
        -h|--help)
            echo "Uso: $0 [--url http://IP_O_DOMINIO:8080] [--open-firewall]"
            exit 0
            ;;
        *)
            echo "Opción no reconocida: $1" >&2
            exit 1
            ;;
    esac
done

echo "=== Instalación CVG Alcasa WordPress (:8080) en Ubuntu ==="

# 1. Verificar Docker y Docker Compose
if ! command -v docker >/dev/null 2>&1; then
    echo "ERROR: Docker no está instalado en este equipo Ubuntu." >&2
    echo "Instálalo primero con:" >&2
    echo "  sudo apt-get update && sudo apt-get install -y docker.io docker-compose-v2 && sudo usermod -aG docker \$USER" >&2
    echo "Luego cierra y vuelve a abrir sesión y ejecuta nuevamente este comando." >&2
    exit 1
fi

if ! docker info >/dev/null 2>&1; then
    echo "ERROR: El servicio Docker no está activo o tu usuario no tiene permisos sobre Docker." >&2
    echo "Ejecuta: sudo systemctl enable --now docker && sudo usermod -aG docker \$USER" >&2
    exit 1
fi

# 2. Crear .env seguro con contraseñas aleatorias si no existe
gen_secret() {
    if command -v openssl >/dev/null 2>&1; then
        openssl rand -base64 24 | tr -dc 'A-Za-z0-9@#%_' | head -c 22
    else
        tr -dc 'A-Za-z0-9@#%_' </dev/urandom | head -c 22
    fi
}

CREATED_ENV=false
if [ ! -f ".env" ]; then
    DB_PASS="$(gen_secret)"
    ROOT_PASS="$(gen_secret)"
    ADMIN_PASS="$(gen_secret)"
    FINAL_URL="${PUBLIC_URL:-http://localhost:8080}"

    cat > .env <<EOF
# Generado automáticamente por install-ubuntu-8080.sh (ignorado por git)
WP_PORT=8080
WP_HOME=${FINAL_URL}
WP_SITEURL=${FINAL_URL}

MYSQL_DATABASE=alcasa_wp
MYSQL_USER=alcasa_user
MYSQL_PASSWORD=${DB_PASS}
MYSQL_ROOT_PASSWORD=${ROOT_PASS}

WP_ADMIN_USER=alcasa_admin
WP_ADMIN_PASSWORD=${ADMIN_PASS}
WP_ADMIN_EMAIL=sistemas@alcasa.com.ve

ALCASA_MEDIA_BASE_URL=/alcasa-media
ALCASA_DATA_BASE_URL=/alcasa-data
ALCASA_NEWS_URL=http://alcasa.com.ve:5001
EOF
    chmod 600 .env
    CREATED_ENV=true
    echo "Archivo wordpress/.env creado con credenciales seguras aleatorias."
elif [ -n "${PUBLIC_URL}" ]; then
    sed -i "s|^WP_HOME=.*|WP_HOME=${PUBLIC_URL}|" .env
    sed -i "s|^WP_SITEURL=.*|WP_SITEURL=${PUBLIC_URL}|" .env
    echo "Actualizadas WP_HOME y WP_SITEURL en wordpress/.env -> ${PUBLIC_URL}"
fi

# 3. Abrir puerto 8080 en UFW si se solicitó
if [ "${OPEN_FIREWALL}" = true ] && command -v ufw >/dev/null 2>&1; then
    echo "Abriendo puerto 8080/tcp en UFW..."
    sudo ufw allow 8080/tcp || true
fi

# 4. Ejecutar inicialización idempotente (Docker + WP-CLI + Tema + Plugin + 6 páginas)
"${SCRIPT_DIR}/setup-local.sh"

# 5. Verificación HTTP en vivo
set -a
# shellcheck disable=SC1091
source .env
set +a

CHECK_URL="http://127.0.0.1:${WP_PORT:-8080}/"
HTTP_STATUS="$(curl -sS -o /dev/null -w "%{http_code}" --max-time 10 "${CHECK_URL}" || echo "000")"
echo ""
if [ "${HTTP_STATUS}" = "200" ] || [ "${HTTP_STATUS}" = "301" ] || [ "${HTTP_STATUS}" = "302" ]; then
    echo "VERIFICACIÓN OK: WordPress respondió HTTP ${HTTP_STATUS} en ${CHECK_URL}"
else
    echo "AVISO: WordPress respondió HTTP ${HTTP_STATUS} en ${CHECK_URL}. Revisa 'docker compose logs wordpress'."
fi

if [ "${CREATED_ENV}" = true ]; then
    echo ""
    echo "Credenciales generadas guardadas en wordpress/.env (permisos 600):"
    echo "  Usuario wp-admin: ${WP_ADMIN_USER}"
    echo "  Revisa la contraseña en wordpress/.env (WP_ADMIN_PASSWORD)."
fi
echo ""
echo "Portal listo en: ${WP_HOME:-http://localhost:8080}"
echo "Para actualizar tras subir cambios a GitHub ejecuta: ./wordpress/scripts/update-ubuntu-8080.sh"
