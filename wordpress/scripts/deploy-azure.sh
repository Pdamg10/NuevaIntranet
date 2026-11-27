#!/usr/bin/env bash
# Despliegue y actualización en UN SOLO COMANDO hacia Microsoft Azure App Service (Linux).
# Empaqueta el tema cvg-alcasa + plugin cvg-alcasa-core + datos públicos (excluyendo datos personales)
# y los sube vía 'az webapp deploy'. Opcionalmente sincroniza media a Azure Blob Storage.
#
# Uso:
#   ./wordpress/scripts/deploy-azure.sh                   # Despliega a producción
#   ./wordpress/scripts/deploy-azure.sh --slot staging    # Despliega primero al slot staging
#   ./wordpress/scripts/deploy-azure.sh --swap            # Intercambia staging -> producción (cutover)
#   ./wordpress/scripts/deploy-azure.sh --sync-media      # Sube media optimizada a Blob Storage + deploy
#   ./wordpress/scripts/deploy-azure.sh --pull            # Hace git pull origin copia antes de desplegar

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WP_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
REPO_DIR="$(cd "${WP_DIR}/.." && pwd)"
ENV_FILE="${WP_DIR}/deploy/azure.env"

TARGET_SLOT=""
DO_SWAP=false
SYNC_MEDIA=false
DO_PULL=false
BRANCH="copia"

while [ "$#" -gt 0 ]; do
    case "$1" in
        --slot)
            TARGET_SLOT="${2:-staging}"
            shift 2
            ;;
        --swap)
            DO_SWAP=true
            shift
            ;;
        --sync-media)
            SYNC_MEDIA=true
            shift
            ;;
        --pull)
            DO_PULL=true
            shift
            ;;
        --branch)
            BRANCH="${2:-copia}"
            shift 2
            ;;
        -h|--help)
            echo "Uso: $0 [--pull] [--branch copia] [--sync-media] [--slot staging | --swap]"
            exit 0
            ;;
        *)
            echo "Opción no reconocida: $1" >&2
            exit 1
            ;;
    esac
done

if ! command -v az >/dev/null 2>&1; then
    echo "ERROR: Azure CLI ('az') no está instalado." >&2
    exit 1
fi

if [ ! -f "${ENV_FILE}" ]; then
    echo "ERROR: Falta ${ENV_FILE}. Copia azure.env.example y complétalo primero." >&2
    exit 1
fi

set -a
# shellcheck disable=SC1090
source "${ENV_FILE}"
set +a

if ! az account show >/dev/null 2>&1; then
    echo "ERROR: No hay sesión activa en Azure CLI. Ejecuta: az login" >&2
    exit 1
fi

if [ -n "${AZURE_SUBSCRIPTION_ID:-}" ]; then
    az account set --subscription "${AZURE_SUBSCRIPTION_ID}"
fi

# Opción: actualizar desde GitHub antes de desplegar
if [ "${DO_PULL}" = true ]; then
    echo "0/4 Actualizando código desde GitHub (rama ${BRANCH})..."
    (cd "${REPO_DIR}" && git fetch origin "${BRANCH}" && git pull --ff-only origin "${BRANCH}")
fi

# Opción: Swap staging -> producción (cutover limpio)
if [ "${DO_SWAP}" = true ]; then
    SLOT_NAME="${TARGET_SLOT:-${AZURE_STAGING_SLOT:-staging}}"
    echo "Ejecutando swap de slot '${SLOT_NAME}' -> producción en ${AZURE_WEBAPP_NAME}..."
    az webapp deployment slot swap \
        --resource-group "${AZURE_RESOURCE_GROUP}" \
        --name "${AZURE_WEBAPP_NAME}" \
        --slot "${SLOT_NAME}" \
        --target-slot production
    echo "Swap completado. Verifica: ${WP_PUBLIC_URL}"
    exit 0
fi

# 1. Empaquetar release limpio (tema + plugin + datos públicos)
echo "1/3 Empaquetando tema, plugin y datos públicos..."
"${SCRIPT_DIR}/package-release.sh"
BUNDLE_ZIP="${WP_DIR}/dist/cvg-alcasa-azure-bundle.zip"

# 2. Sincronizar media a Azure Blob Storage (opcional, excluyendo PNG crudos pesados de productos/)
if [ "${SYNC_MEDIA}" = true ]; then
    echo "2/3 Sincronizando assets/img y assets/media hacia Azure Blob Storage (${AZURE_STORAGE_ACCOUNT}/${AZURE_STORAGE_CONTAINER})..."
    STORAGE_KEY="$(az storage account keys list --resource-group "${AZURE_RESOURCE_GROUP}" --account-name "${AZURE_STORAGE_ACCOUNT}" --query '[0].value' -o tsv)"

    MEDIA_STAGE="$(mktemp -d)"
    trap 'rm -rf "${MEDIA_STAGE}"' EXIT
    cp -R "${REPO_DIR}/assets/img" "${MEDIA_STAGE}/img"
    rm -f "${MEDIA_STAGE}/img/productos/"*.png

    # Subir imágenes (solo optimizadas, sin PNG crudos de 25 MB en productos/)
    az storage blob upload-batch \
        --account-name "${AZURE_STORAGE_ACCOUNT}" \
        --account-key "${STORAGE_KEY}" \
        --destination "${AZURE_STORAGE_CONTAINER}" \
        --destination-path "img" \
        --source "${MEDIA_STAGE}/img" \
        --overwrite true \
        --output none

    # Subir videos MP4 y catálogos PDF
    az storage blob upload-batch \
        --account-name "${AZURE_STORAGE_ACCOUNT}" \
        --account-key "${STORAGE_KEY}" \
        --destination "${AZURE_STORAGE_CONTAINER}" \
        --destination-path "media" \
        --source "${REPO_DIR}/assets/media" \
        --overwrite true \
        --output none
    echo "  Media sincronizada en https://${AZURE_STORAGE_ACCOUNT}.blob.core.windows.net/${AZURE_STORAGE_CONTAINER}/"
else
    echo "2/3 Saltando sincronización de media (usa --sync-media si cambiaste imágenes o videos)."
fi

# 3. Desplegar el bundle ZIP al App Service (producción o slot staging)
DEPLOY_ARGS=(
    --resource-group "${AZURE_RESOURCE_GROUP}"
    --name "${AZURE_WEBAPP_NAME}"
    --src-path "${BUNDLE_ZIP}"
    --type zip
    --clean false
)

if [ -n "${TARGET_SLOT}" ]; then
    echo "3/3 Desplegando bundle al slot '${TARGET_SLOT}' de ${AZURE_WEBAPP_NAME}..."
    DEPLOY_ARGS+=(--slot "${TARGET_SLOT}")
else
    echo "3/3 Desplegando bundle a producción en ${AZURE_WEBAPP_NAME}..."
fi

az webapp deploy "${DEPLOY_ARGS[@]}"

echo ""
echo "Despliegue completado correctamente."
echo "Verifica el portal en: ${WP_PUBLIC_URL}"
echo "Diagnóstico admin:     ${WP_PUBLIC_URL}/wp-admin/admin.php?page=cvg-alcasa-bandejas&tab=sistema"
