# Guía Rápida — Instalación y Actualización en Ubuntu (`:8080`)

Pensada para el equipo que administra el servidor **Linux Ubuntu**: instalar desde cero en **1 comando** y actualizar tras cada cambio en GitHub en **1 comando**, sin perder base de datos ni CVs subidos.

---

## 1. Requisitos únicos en el equipo Ubuntu

Si es un Ubuntu recién instalado, instala Git y Docker una sola vez:

```bash
sudo apt-get update && sudo apt-get install -y git docker.io docker-compose-v2 curl openssl
sudo systemctl enable --now docker
sudo usermod -aG docker "$USER"
```
*(Cierra sesión y vuelve a entrar para que el grupo `docker` surta efecto.)*

---

## 2. Diagnóstico previo (opcional, 5 segundos)

Verifica puerto `8080`, herramientas y conectividad con `alcasa.com.ve:5001`:

```bash
git clone -b copia https://github.com/Pdamg10/NuevaIntranet.git
cd NuevaIntranet
./wordpress/scripts/check-network.sh
```

---

## 3. Instalar y levantar WordPress en `:8080` (1 solo comando)

**En local (`http://localhost:8080`):**
```bash
./wordpress/scripts/install-ubuntu-8080.sh
```

**En servidor Ubuntu accesible por red/IP (abriendo puerto `8080` en UFW):**
```bash
./wordpress/scripts/install-ubuntu-8080.sh --url http://IP_DEL_SERVIDOR:8080 --open-firewall
```

Qué hace automáticamente:
- Genera `wordpress/.env` (permisos `600`) con contraseñas aleatorias seguras.
- Levanta MySQL 8 + WordPress PHP 8.2 en el puerto `8080` con límites de carga `12M/25M`.
- Instala WordPress en español (`America/Caracas`), activa el tema **`cvg-alcasa`** y el plugin **`cvg-alcasa-core`**.
- Crea las 6 páginas (`/`, `/productos/`, `/galeria/`, `/prensa/`, `/periodico/`, `/cumpleaneros/`), permalinks `/%postname%/` y las tablas MySQL (`wp_alcasa_contactos`, `wp_alcasa_postulaciones`).
- Verifica con HTTP que el portal responda en `:8080`.

> **Acceso al panel administrativo:** `http://<servidor>:8080/wp-admin/` (usuario `alcasa_admin`; la contraseña generada queda en `wordpress/.env` bajo `WP_ADMIN_PASSWORD`).

---

## 4. Actualizar cuando se suba un cambio a GitHub (1 solo comando)

Cada vez que Pham o el equipo suban cambios a la rama `copia`, en el servidor Ubuntu solo ejecutan:

```bash
cd NuevaIntranet
./wordpress/scripts/update-ubuntu-8080.sh
```

- Descarga los últimos cambios (`git pull --ff-only origin copia`).
- Refresca el tema, el plugin, los transients y los enlaces permanentes.
- **Conserva intactos** todos los registros de MySQL y todos los CVs almacenados en el volumen privado `wp_private_storage`.

---

## 5. Comandos útiles de operación

```bash
# Ver logs en vivo
cd wordpress && docker compose logs -f wordpress

# Reiniciar servicios sin perder datos
cd wordpress && docker compose restart

# Respaldo rápido de la base de datos MySQL
cd wordpress && docker compose exec -T db sh -c 'mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > "backup-$(date +%Y%m%d).sql"
```
