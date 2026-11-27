#!/usr/bin/env bash
# Aprovisiona desde cero la infraestructura recomendada en Microsoft Azure (idempotente):
#   - Resource Group
#   - Azure Database for MySQL (Flexible Server) + base de datos WordPress
#   - Azure Storage Account + contenedor público de solo lectura para media (Blob Storage)
#   - Azure App Service Plan (Linux) + Web App PHP 8.2 + Slot 'staging'
#   - App Settings seguras (sin credenciales hardcodeadas en el repositorio)
#
# Requisito previo en Ubuntu:
#   az login
#   cp wordpress/deploy/azure.env.example wordpress/deploy/azure.env
#   nano wordpress/deploy/azure.env
#   ./wordpress/deploy/azure-provision.sh

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${SCRIPT_DIR}/azure.env"

if ! command -v az >/dev/null 2>&1; then
    echo "ERROR: Azure CLI ('az') no está instalado en este equipo Ubuntu." >&2
    echo "Instálalo con: curl -sL https://aka.ms/InstallAzureCLIDeb | sudo bash" >&2
    exit 1
fi

if [ ! -f "${ENV_FILE}" ]; then
    echo "ERROR: Falta ${ENV_FILE}." >&2
    echo "Crea tu configuración local con:" >&2
    echo "  cp ${SCRIPT_DIR}/azure.env.example ${ENV_FILE} && nano ${ENV_FILE}" >&2
    exit 1
fi

set -a
# shellcheck disable=SC1090
source "${ENV_FILE}"
set +a

if ! az account show >/dev/null 2>&1; then
    echo "ERROR: No hay sesión activa en Azure CLI. Ejecuta primero: az login" >&2
    exit 1
fi

if [ -n "${AZURE_SUBSCRIPTION_ID:-}" ]; then
    az account set --subscription "${AZURE_SUBSCRIPTION_ID}"
fi

if [ -z "${AZURE_MYSQL_ADMIN_PASSWORD:-}" ]; then
    AZURE_MYSQL_ADMIN_PASSWORD="$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9@#%_' | head -c 22)A9#"
    sed -i "s|^AZURE_MYSQL_ADMIN_PASSWORD=.*|AZURE_MYSQL_ADMIN_PASSWORD=\"${AZURE_MYSQL_ADMIN_PASSWORD}\"|" "${ENV_FILE}"
    chmod 600 "${ENV_FILE}"
    echo "Generada clave segura para MySQL Flexible Server y guardada en wordpress/deploy/azure.env (permisos 600)."
fi

echo "1/6 Creando/verificando Resource Group (${AZURE_RESOURCE_GROUP}) en ${AZURE_LOCATION}..."
az group create \
    --name "${AZURE_RESOURCE_GROUP}" \
    --location "${AZURE_LOCATION}" \
    --output none

echo "2/6 Creando/verificando Azure Database for MySQL Flexible Server (${AZURE_MYSQL_SERVER})..."
if ! az mysql flexible-server show --resource-group "${AZURE_RESOURCE_GROUP}" --name "${AZURE_MYSQL_SERVER}" >/dev/null 2>&1; then
    az mysql flexible-server create \
        --resource-group "${AZURE_RESOURCE_GROUP}" \
        --name "${AZURE_MYSQL_SERVER}" \
        --location "${AZURE_LOCATION}" \
        --admin-user "${AZURE_MYSQL_ADMIN_USER}" \
        --admin-password "${AZURE_MYSQL_ADMIN_PASSWORD}" \
        --sku-name "${AZURE_MYSQL_SKU}" \
        --tier Burstable \
        --version "${AZURE_MYSQL_VERSION}" \
        --storage-size 32 \
        --public-access 0.0.0.0 \
        --yes \
        --output none
fi

az mysql flexible-server db create \
    --resource-group "${AZURE_RESOURCE_GROUP}" \
    --server-name "${AZURE_MYSQL_SERVER}" \
    --database-name "${AZURE_MYSQL_DB}" \
    --charset utf8mb4 \
    --collation utf8mb4_unicode_ci \
    --output none

MYSQL_HOST="${AZURE_MYSQL_SERVER}.mysql.database.azure.com"

echo "3/6 Creando/verificando Storage Account (${AZURE_STORAGE_ACCOUNT}) para media..."
if ! az storage account show --resource-group "${AZURE_RESOURCE_GROUP}" --name "${AZURE_STORAGE_ACCOUNT}" >/dev/null 2>&1; then
    az storage account create \
        --resource-group "${AZURE_RESOURCE_GROUP}" \
        --name "${AZURE_STORAGE_ACCOUNT}" \
        --location "${AZURE_LOCATION}" \
        --sku Standard_LRS \
        --kind StorageV2 \
        --allow-blob-public-access true \
        --output none
fi

STORAGE_KEY="$(az storage account keys list --resource-group "${AZURE_RESOURCE_GROUP}" --account-name "${AZURE_STORAGE_ACCOUNT}" --query '[0].value' -o tsv)"
az storage container create \
    --name "${AZURE_STORAGE_CONTAINER}" \
    --account-name "${AZURE_STORAGE_ACCOUNT}" \
    --account-key "${STORAGE_KEY}" \
    --public-access blob \
    --output none

MEDIA_BASE_URL="https://${AZURE_STORAGE_ACCOUNT}.blob.core.windows.net/${AZURE_STORAGE_CONTAINER}"

echo "4/6 Creando/verificando App Service Plan Linux (${AZURE_APP_PLAN}) y Web App (${AZURE_WEBAPP_NAME})..."
if ! az appservice plan show --resource-group "${AZURE_RESOURCE_GROUP}" --name "${AZURE_APP_PLAN}" >/dev/null 2>&1; then
    az appservice plan create \
        --resource-group "${AZURE_RESOURCE_GROUP}" \
        --name "${AZURE_APP_PLAN}" \
        --location "${AZURE_LOCATION}" \
        --is-linux \
        --sku "${AZURE_APP_SKU}" \
        --output none
fi

if ! az webapp show --resource-group "${AZURE_RESOURCE_GROUP}" --name "${AZURE_WEBAPP_NAME}" >/dev/null 2>&1; then
    az webapp create \
        --resource-group "${AZURE_RESOURCE_GROUP}" \
        --plan "${AZURE_APP_PLAN}" \
        --name "${AZURE_WEBAPP_NAME}" \
        --runtime "PHP:8.2" \
        --output none
fi

az webapp update \
    --resource-group "${AZURE_RESOURCE_GROUP}" \
    --name "${AZURE_WEBAPP_NAME}" \
    --https-only true \
    --output none

echo "5/6 Configurando App Settings y variables de entorno de CVG Alcasa..."
az webapp config appsettings set \
    --resource-group "${AZURE_RESOURCE_GROUP}" \
    --name "${AZURE_WEBAPP_NAME}" \
    --settings \
        WP_HOME="${WP_PUBLIC_URL}" \
        WP_SITEURL="${WP_PUBLIC_URL}" \
        WORDPRESS_DB_HOST="${MYSQL_HOST}:3306" \
        WORDPRESS_DB_NAME="${AZURE_MYSQL_DB}" \
        WORDPRESS_DB_USER="${AZURE_MYSQL_ADMIN_USER}" \
        WORDPRESS_DB_PASSWORD="${AZURE_MYSQL_ADMIN_PASSWORD}" \
        ALCASA_MEDIA_BASE_URL="${MEDIA_BASE_URL}" \
        ALCASA_DATA_BASE_URL="/alcasa-data" \
        ALCASA_DATA_DIR="/home/site/wwwroot/alcasa-data" \
        ALCASA_PRIVATE_STORAGE="/home/site/alcasa-private" \
        ALCASA_NEWS_URL="${ALCASA_NEWS_URL}" \
        ALCASA_MAIL_CONTACTO="${ALCASA_MAIL_CONTACTO:-}" \
        ALCASA_MAIL_RRHH="${ALCASA_MAIL_RRHH:-}" \
        PHP_INI_SCAN_DIR="/usr/local/etc/php/conf.d:/home/site/ini" \
    --output none

echo "6/6 Verificando slot de staging (${AZURE_STAGING_SLOT}) si el plan lo soporta..."
if az webapp deployment slot list --resource-group "${AZURE_RESOURCE_GROUP}" --name "${AZURE_WEBAPP_NAME}" >/dev/null 2>&1; then
    az webapp deployment slot create \
        --resource-group "${AZURE_RESOURCE_GROUP}" \
        --name "${AZURE_WEBAPP_NAME}" \
        --slot "${AZURE_STAGING_SLOT}" \
        --configuration-source "${AZURE_WEBAPP_NAME}" \
        --output none 2>/dev/null || echo "  INFO: El SKU ${AZURE_APP_SKU} no incluye deployment slots (requiere Standard S1+); se desplegará directo a producción."
fi

echo ""
echo "============================================================"
echo " Infraestructura Azure lista para CVG Alcasa WordPress"
echo "============================================================"
echo "  Web App:      ${WP_PUBLIC_URL}"
echo "  MySQL Host:   ${MYSQL_HOST}"
echo "  Media Blob:   ${MEDIA_BASE_URL}"
echo ""
echo "Siguiente paso: ejecuta ./wordpress/scripts/deploy-azure.sh --sync-media"
