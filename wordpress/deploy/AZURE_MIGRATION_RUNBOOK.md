# Runbook de Migración y Despliegue — WordPress CVG Alcasa en Microsoft Azure

Guía operativa completa para el equipo de infraestructura en **Ubuntu + Microsoft Azure** (**Azure App Service Linux + Azure Database for MySQL Flexible Server + Azure Blob Storage**).

---

## 1. Arquitectura en Microsoft Azure

| Componente | Recurso Azure | Rol |
| --- | --- | --- |
| Cómputo Web | **Azure App Service (Linux · PHP 8.2)** | Ejecuta WordPress, tema `cvg-alcasa` y plugin `cvg-alcasa-core` sobre HTTPS (`443`) |
| Base de Datos | **Azure Database for MySQL (Flexible Server)** | Almacena WordPress + tablas `wp_alcasa_contactos` y `wp_alcasa_postulaciones` |
| Media Estática | **Azure Blob Storage** (`alcasa-media`) | Sirve los ~174 MB de imágenes, videos MP4 y catálogos PDF vía `ALCASA_MEDIA_BASE_URL` |
| CVs Privados | `/home/site/alcasa-private/cv` (fuera de `wwwroot`) | Persistente en App Service y nunca accesible por HTTP directo; descarga solo por `manage_options` |
| Noticias `:5001` | Proxy `wp_remote_get()` + Transients (120 s) | Si `:5001` no responde desde Azure, sirve respaldo `alcasa-data/noticias.json` sin caída |

---

## 2. Requisitos previos en el equipo Ubuntu que despliega a Azure

```bash
# Instalar Azure CLI (si no está instalado)
curl -sL https://aka.ms/InstallAzureCLIDeb | sudo bash

# Iniciar sesión en Microsoft Azure
az login
```

---

## 3. Aprovisionar Azure desde cero (1 comando)

```bash
cd NuevaIntranet
cp wordpress/deploy/azure.env.example wordpress/deploy/azure.env
nano wordpress/deploy/azure.env   # Ajustar nombres únicos globales de WebApp, MySQL y Storage
./wordpress/deploy/azure-provision.sh
```

> `wordpress/deploy/azure.env` está ignorado en `wordpress/.gitignore`. Nunca subas contraseñas ni claves a GitHub.

---

## 4. Primer despliegue y configuración de WordPress en Azure

1. Instala el núcleo base de WordPress en el App Service (o usa la imagen gestionada *WordPress on App Service* apuntando al MySQL Flexible Server aprovisionado) y asegura en `wp-config.php` las constantes leídas desde App Settings:
   ```php
   define( 'WP_HOME', getenv( 'WP_HOME' ) ?: 'https://cvg-alcasa-portal.azurewebsites.net' );
   define( 'WP_SITEURL', getenv( 'WP_SITEURL' ) ?: 'https://cvg-alcasa-portal.azurewebsites.net' );
   define( 'ALCASA_MEDIA_BASE_URL', getenv( 'ALCASA_MEDIA_BASE_URL' ) ?: '/alcasa-media' );
   define( 'ALCASA_DATA_BASE_URL', getenv( 'ALCASA_DATA_BASE_URL' ) ?: '/alcasa-data' );
   define( 'ALCASA_DATA_DIR', getenv( 'ALCASA_DATA_DIR' ) ?: '/home/site/wwwroot/alcasa-data' );
   define( 'ALCASA_PRIVATE_STORAGE', getenv( 'ALCASA_PRIVATE_STORAGE' ) ?: '/home/site/alcasa-private' );
   define( 'ALCASA_NEWS_URL', getenv( 'ALCASA_NEWS_URL' ) ?: 'http://alcasa.com.ve:5001' );
   ```
2. Sube el tema, el plugin, los datos públicos y toda la media a Blob Storage en **1 solo comando**:
   ```bash
   ./wordpress/scripts/deploy-azure.sh --sync-media
   ```
3. Entra a `https://<tu-app>.azurewebsites.net/wp-admin/`:
   - Activa el tema **CVG Alcasa v2** (`cvg-alcasa`).
   - Activa el plugin **CVG Alcasa Core** (`cvg-alcasa-core`) — crea automáticamente las tablas MySQL e importa los 8 contactos semilla.
   - Crea las 6 páginas institucionales con sus plantillas (`Inicio`, `Productos`, `Galería`, `Prensa`, `Periódico`, `Cumpleañeros`) y activa enlaces permanentes **Nombre de la entrada (`/%postname%/`)**.
   - Abre **CVG Alcasa → Estado del Sistema y Red (:5001)** y confirma: MySQL activa, almacenamiento privado escribible y si `:5001` responde en vivo o en modo respaldo.

---

## 5. Subir actualizaciones diarias a Azure (1 solo comando)

Cada vez que se suban mejoras a GitHub (`copia`), desde el equipo Ubuntu:

- **Opción A — Actualización rápida de código (tema/plugin/datos):**
  ```bash
  ./wordpress/scripts/deploy-azure.sh --pull
  ```
- **Opción B — Si también agregaste fotos, videos o PDFs nuevos:**
  ```bash
  ./wordpress/scripts/deploy-azure.sh --pull --sync-media
  ```
- **Opción C — Despliegue seguro con Staging Slot (recomendado en producción):**
  ```bash
  ./wordpress/scripts/deploy-azure.sh --pull --slot staging
  # 1) Valida en https://<tu-app>-staging.azurewebsites.net
  # 2) Promueve a producción sin caída:
  ./wordpress/scripts/deploy-azure.sh --swap
  ```

### Rollback instantáneo (si algo falla tras el swap)
Ejecuta nuevamente `./wordpress/scripts/deploy-azure.sh --swap` para devolver el slot anterior a producción en segundos.

---

## 6. Datos personales (Cumpleañeros y CVs)

- `data/cumpleaneros.json` (3.543 trabajadores) **no se versiona en GitHub** ni se incluye en el ZIP automático. Para habilitar **Nuestros Cumpleañeros** en Azure, súbelo de forma segura por SFTP/SSH al directorio `/home/site/wwwroot/alcasa-data/cumpleaneros.json` y sus retratos al contenedor Blob `alcasa-media/img/cumpleaneros/`.
- Si en el futuro escalas App Service a **múltiples instancias**, monta un **Azure File Share** en `/home/site/alcasa-private` o migra `CVG_Alcasa_CV_Storage` a un contenedor privado de Azure Blob Storage con SAS temporal.
