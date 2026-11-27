#!/usr/bin/env bash
# Diagnóstico rápido de entorno Ubuntu (:8080), herramientas Azure y conectividad hacia alcasa.com.ve:5001.

set -euo pipefail

NEWS_URL="${ALCASA_NEWS_URL:-http://alcasa.com.ve:5001/?page=1}"
TARGET_PORT="${WP_PORT:-8080}"

echo "============================================================"
echo " Diagnóstico CVG Alcasa — Entorno Ubuntu / WordPress / Azure"
echo "============================================================"

echo ""
echo "[1/5] Sistema operativo:"
if [ -f /etc/os-release ]; then
    grep -E "^(PRETTY_NAME|VERSION_ID)=" /etc/os-release || true
else
    uname -a
fi

echo ""
echo "[2/5] Herramientas instaladas:"
for bin in git docker docker-compose az php curl zip unzip openssl; do
    if command -v "${bin}" >/dev/null 2>&1; then
        printf "  OK   %-15s -> %s\n" "${bin}" "$(command -v "${bin}")"
    else
        printf "  WARN %-15s -> no instalado\n" "${bin}"
    fi
done

echo ""
echo "[3/5] Estado del puerto ${TARGET_PORT}:"
if command -v ss >/dev/null 2>&1; then
    OCCUPIED="$(ss -tulpn 2>/dev/null | grep -E ":${TARGET_PORT}\b" || true)"
    if [ -n "${OCCUPIED}" ]; then
        echo "  AVISO: El puerto ${TARGET_PORT} está en uso:"
        echo "${OCCUPIED}"
    else
        echo "  OK: El puerto ${TARGET_PORT} está libre."
    fi
else
    echo "  INFO: 'ss' no disponible para inspeccionar puertos."
fi

echo ""
echo "[4/5] Firewall UFW (si está instalado):"
if command -v ufw >/dev/null 2>&1; then
    ufw status 2>/dev/null || echo "  INFO: Ejecuta con sudo para consultar 'sudo ufw status'."
else
    echo "  INFO: ufw no instalado."
fi

echo ""
echo "[5/5] Conectividad hacia servidor de noticias (${NEWS_URL}):"
if command -v curl >/dev/null 2>&1; then
    HTTP_CODE="$(curl -sS -o /dev/null -w "%{http_code}" --max-time 6 "${NEWS_URL}" || echo "000")"
    if [ "${HTTP_CODE}" = "200" ] || [ "${HTTP_CODE}" = "301" ] || [ "${HTTP_CODE}" = "302" ]; then
        echo "  OK: Servidor de noticias accesible (HTTP ${HTTP_CODE})."
    else
        echo "  AVISO: No se obtuvo respuesta válida desde ${NEWS_URL} (HTTP ${HTTP_CODE})."
        echo "         El plugin cvg-alcasa-core usará automáticamente el respaldo local data/noticias.json."
    fi
else
    echo "  WARN: curl no instalado."
fi

echo ""
echo "Diagnóstico finalizado."
