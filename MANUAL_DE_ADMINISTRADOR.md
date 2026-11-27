# Manual de Administrador — Portal Institucional CVG Alcasa v2

Guía técnica y operativa dirigida al equipo de **Sistemas, Informática e Infraestructura** de **CVG Aluminio del Caroní, S.A. (CVG Alcasa)** para administrar, mantener, respaldar y desplegar el portal tanto en **Linux Ubuntu (`:8080`)** como en **Microsoft Azure** (y en el entorno base HTML/PHP).

---

## 1. Arquitectura General del Repositorio

El repositorio (`Pdamg10/NuevaIntranet`, rama activa `copia`) mantiene dos entornos desacoplados:

1. **Entorno Base HTML/PHP (raíz del repositorio):**
   - Vistas en `/*.html` y `sitio/Empresa/CA/index.html`.
   - Backend ligero en `api/` (`contacto.php`, `cv.php`, `noticias.php`, `admin_datos.php`, `prensa_admin.php`, `_lib.php`).
   - Datos estáticos en `data/contenido.json` y `data/noticias.json`; datos runtime privados en `storage/` (excluidos de Git).
2. **Entorno Productivo WordPress + Azure (`wordpress/`):**
   - **Tema a medida (`wordpress/wp-content/themes/cvg-alcasa/`):** capa exclusivamente visual (`header.php`, `footer.php`, `functions.php`, 6 plantillas y encolado estándar `wp_enqueue_*`).
   - **Plugin funcional (`wordpress/wp-content/plugins/cvg-alcasa-core/`):** capa de negocio y datos:
     - Tablas MySQL (`$wpdb` + `dbDelta()`): `wp_alcasa_contactos` y `wp_alcasa_postulaciones`.
     - API REST: `/wp-json/alcasa/v1/{contacto,cv,noticias,contenido,cumpleaneros,health}`.
     - Almacenamiento privado de CVs fuera de la raíz web (`ALCASA_PRIVATE_STORAGE`).
     - Proxy resiliente hacia `http://alcasa.com.ve:5001` con **WordPress Transients** (`120 s`) y respaldo automático `data/noticias.json`.
     - Panel administrativo nativo en **`wp-admin`** bajo el rol `manage_options`.

---

## 2. Administración de Bandejas (Contactos Comerciales y Postulaciones CV)

### 2.1 En WordPress (recomendado — Ubuntu `:8080` y Microsoft Azure)
1. Ingresa a `http://<servidor>:8080/wp-admin/` (o `https://<tu-app>.azurewebsites.net/wp-admin/`) con un usuario con rol **Administrador** (`manage_options`).
2. En el menú lateral izquierdo selecciona **CVG Alcasa**.
3. Dispones de **3 pestañas**:
   - **Contactos Comerciales:** buscar por nombre/correo/empresa/producto, **Exportar CSV** (con protección automática contra inyección de fórmulas `=+-@`) y eliminar registros con confirmación.
   - **Postulaciones de CV:** buscar por nombre/cédula/área, pulsar **Descargar CV** (descarga autenticada vía `admin-post.php` + nonce desde almacenamiento privado, nunca expuesta por URL directa), **Exportar CSV** y eliminar postulación (borra también el archivo físico adjunto).
   - **Estado del Sistema y Red (`:5001`):** muestra versión del plugin/PHP, conteo de registros en MySQL, estado y modo del almacenamiento privado de CVs y prueba en vivo de conectividad hacia `http://alcasa.com.ve:5001/?page=1` (indicando si opera en modo **En vivo** o en **Respaldo local `noticias.json`**).

### 2.2 Gestión de Usuarios Administradores en WordPress
- Crea o revoca cuentas únicamente desde **wp-admin → Usuarios**.
- Asigna el rol **Administrador** solo al personal autorizado de Sistemas/Comercialización/RRHH.
- **No** existen contraseñas embebidas en código fuente en la versión WordPress.

### 2.3 En el Entorno Base HTML/PHP (`/contactos.html`)
- Acceso restringido exclusivamente a la cuenta `admin` configurada en `storage/usuarios_config.json` (archivo local excluido de Git).
- **Importante de seguridad:** en cualquier servidor real donde se use la versión PHP plana, cambia inmediatamente la contraseña de `storage/usuarios_config.json` y asegúrate de que `storage/.htaccess` (Apache) o `web.config` (IIS) bloqueen el acceso HTTP directo a `/storage/`.

---

## 3. Operación en Servidor Linux Ubuntu (`:8080`)

Guía completa en [wordpress/deploy/UBUNTU_8080_QUICKSTART.md](wordpress/deploy/UBUNTU_8080_QUICKSTART.md).

### 3.1 Instalación inicial en 1 comando
Requisitos previos: `git`, `docker.io`, `docker-compose-v2`, `curl`, `openssl`.

```bash
git clone -b copia https://github.com/Pdamg10/NuevaIntranet.git
cd NuevaIntranet
./wordpress/scripts/check-network.sh
./wordpress/scripts/install-ubuntu-8080.sh --url http://IP_DEL_SERVIDOR:8080 --open-firewall
```

- Las credenciales generadas automáticamente quedan guardadas únicamente en `wordpress/.env` (`chmod 600`, ignorado por Git).
- Los límites PHP (`upload_max_filesize=12M`, `post_max_size=25M`) se aplican automáticamente desde `wordpress/config/uploads.ini`.

### 3.2 Actualizar el portal en Ubuntu tras cambios en GitHub (1 comando)
```bash
cd NuevaIntranet
./wordpress/scripts/update-ubuntu-8080.sh
```
Descarga los cambios de `origin/copia`, refresca tema/plugin, limpia Transients/caché y enlaces permanentes **sin perder MySQL ni CVs subidos**.

---

## 4. Operación y Despliegue en Microsoft Azure

Guía completa en [wordpress/deploy/AZURE_MIGRATION_RUNBOOK.md](wordpress/deploy/AZURE_MIGRATION_RUNBOOK.md).

### 4.1 Aprovisionamiento inicial (una sola vez)
```bash
az login
cp wordpress/deploy/azure.env.example wordpress/deploy/azure.env
nano wordpress/deploy/azure.env
./wordpress/deploy/azure-provision.sh
```
Crea **Resource Group**, **Azure Database for MySQL Flexible Server**, **Azure Storage Account + Blob Container (`alcasa-media`)**, **App Service Plan Linux**, **Web App PHP 8.2** con **HTTPS Only**, App Settings seguras y slot `staging` (si el SKU lo soporta).

### 4.2 Desplegar o actualizar cambios en Azure (1 comando)
```bash
# Subir código (tema + plugin + datos públicos) al slot staging:
./wordpress/scripts/deploy-azure.sh --pull --slot staging

# Si también cambiaste fotos, videos MP4 o PDFs (sincroniza a Blob Storage sin PNG crudos de 25 MB):
./wordpress/scripts/deploy-azure.sh --pull --sync-media --slot staging

# Promover staging a producción sin caída (cutover):
./wordpress/scripts/deploy-azure.sh --swap
```
> **Rollback instantáneo:** si detectas un problema tras el cutover, vuelve a ejecutar `./wordpress/scripts/deploy-azure.sh --swap`.

### 4.3 Empaquetado manual de ZIPs (si necesitas instalar vía wp-admin)
```bash
./wordpress/scripts/package-release.sh
# Genera en wordpress/dist/ (ignorado por Git):
#   - cvg-alcasa-theme.zip
#   - cvg-alcasa-core-plugin.zip
#   - cvg-alcasa-azure-bundle.zip (excluye cumpleaneros.json, .env y storage/)
```

---

## 5. Mantenimiento de Contenidos y Datos

### 5.1 Actualizar Productos, Galería o Preguntas Frecuentes (`data/contenido.json`)
1. Edita `data/contenido.json` respetando formato JSON válido (puedes validarlo con `python3 -m json.tool data/contenido.json >/dev/null`).
2. Coloca nuevas imágenes optimizadas en `assets/img/productos/` o `assets/img/galeria/` (evita subir PNG crudos de varios MB; usa JPG/WebP optimizados).
3. En Ubuntu ejecuta `./wordpress/scripts/update-ubuntu-8080.sh`; en Azure ejecuta `./wordpress/scripts/deploy-azure.sh --pull --sync-media`.

### 5.2 Actualizar «Nuestros Cumpleañeros» (`data/cumpleaneros.json`)
- Este archivo contiene datos personales de los 3.543 trabajadores y **nunca se versiona en Git**.
- Regeneración local desde `DATA.csv` + `FotosAdabas`:
  ```bash
  python3 tools/generar_cumpleaneros.py
  ```
- **En Ubuntu (`:8080`):** el contenedor monta `../data` y `../assets/img`, por lo que al regenerar `data/cumpleaneros.json` y `assets/img/cumpleaneros/` queda disponible de inmediato.
- **En Microsoft Azure:** sube `data/cumpleaneros.json` por SFTP/SSH a `/home/site/wwwroot/alcasa-data/cumpleaneros.json` y los retratos nuevos al contenedor Blob `alcasa-media/img/cumpleaneros/`.

### 5.3 Notificaciones por Correo Electrónico (Opcional)
Para recibir avisos automáticos al registrarse un contacto o postulación de CV en WordPress:
- Define `ALCASA_MAIL_CONTACTO` y `ALCASA_MAIL_RRHH` en `wordpress/.env` (Ubuntu) o en **App Settings** (Azure).
- Configura un plugin SMTP en WordPress (ej. *WP Mail SMTP* con el servidor SMTP institucional o Azure Communication Services) para garantizar entrega.

---

## 6. Respaldos, Seguridad y Monitoreo

### 6.1 Respaldos
- **Ubuntu (`:8080`):**
  ```bash
  # Respaldo de MySQL
  cd wordpress
  docker compose exec -T db sh -c 'mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > "backup-alcasa-$(date +%Y%m%d).sql"

  # Respaldo del volumen privado de CVs
  docker run --rm -v wordpress_wp_private_storage:/src:ro -v "$(pwd)":/dst alpine tar czf "/dst/backup-cvs-$(date +%Y%m%d).tar.gz" -C /src .
  ```
- **Microsoft Azure:** habilitar **Automated Backups** en **Azure Database for MySQL Flexible Server** (retención recomendada ≥ 14 días) y respaldo periódico de `/home/site/alcasa-private/` (o Azure File Share / Blob Storage privado).

### 6.2 Checklist de Seguridad Obligatoria
- [ ] Verificar que `wordpress/.env` y `wordpress/deploy/azure.env` tengan permisos `600` y nunca se añadan a Git.
- [ ] Mantener HTTPS obligatorio (`--https-only true` en Azure; proxy inverso con TLS si Ubuntu se expone a internet).
- [ ] Verificar en **wp-admin → CVG Alcasa → Estado del Sistema** que el modo de CVs reporte `private-outside-wwwroot`.
- [ ] Mantener actualizados el núcleo de WordPress y PHP 8.2 con parches de seguridad.

### 6.3 Diagnóstico Rápido ante Incidencias

| Síntoma | Causa probable | Acción correctiva |
| :--- | :--- | :--- |
| Noticias muestran solo respaldo local | `alcasa.com.ve:5001` inaccesible desde el servidor | Revisar **wp-admin → CVG Alcasa → Estado del Sistema** y `./wordpress/scripts/check-network.sh`; si `:5001` es interno, configurar VNet/VPN en Azure o actualizar `data/noticias.json`. |
| Error `413` o falla al subir CV > 2 MB | Límite PHP por defecto sin `uploads.ini` | Confirmar `upload_max_filesize=12M` y `post_max_size=25M` en `wordpress/config/uploads.ini` o en `/home/site/ini/uploads.ini` (Azure). |
| Páginas `/productos/` o `/galeria/` devuelven `404` | Permalinks sin refrescar | Ir a **wp-admin → Ajustes → Enlaces permanentes → Guardar cambios** (o correr `wp rewrite flush --hard`). |
| Imágenes o videos no cargan en Azure | `ALCASA_MEDIA_BASE_URL` incorrecta o media no subida | Verificar App Setting `ALCASA_MEDIA_BASE_URL` y ejecutar `./wordpress/scripts/deploy-azure.sh --sync-media`. |
