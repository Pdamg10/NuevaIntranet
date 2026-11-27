# CVG Aluminio del Caroní, S.A. (CVG Alcasa) — Portal Institucional v2

Portal web institucional de **CVG Aluminio del Caroní, S.A. (CVG Alcasa)** en la rama `copia`, compuesto por dos capas complementarias dentro del mismo repositorio:

1. **Portal Web Base (raíz del repositorio):** HTML5, CSS3 y JavaScript vanilla modular con backend PHP (`api/`), totalmente funcional en desarrollo local (`localhost:8000` / `:8070`).
2. **Paquete WordPress + Microsoft Azure + Ubuntu (`wordpress/`):** Tema a medida (`cvg-alcasa`), plugin funcional desacoplado (`cvg-alcasa-core` con tablas MySQL `$wpdb`, API REST `/wp-json/alcasa/v1/*`, almacenamiento privado de CVs, proxy de noticias `:5001` y panel nativo en `wp-admin`), entorno Docker en puerto **`8080`** y scripts de instalación/actualización en **un solo comando** para **Linux Ubuntu** y **Microsoft Azure** (**App Service Linux + Azure Database for MySQL Flexible Server + Azure Blob Storage**).

---

## Documentación del Proyecto

| Documento | Propósito |
| :--- | :--- |
| [MANUAL_DE_USUARIO.md](MANUAL_DE_USUARIO.md) | Guía paso a paso para visitantes, clientes, postulantes y trabajadores (navegación, catálogo, galería 1:1, prensa, periódico digital, formularios y cumpleañeros). |
| [MANUAL_DE_ADMINISTRADOR.md](MANUAL_DE_ADMINISTRADOR.md) | Guía técnica y operativa para el equipo de Sistemas / TI (gestión de bandejas, descarga segura de CVs, actualización de catálogo y cumpleañeros, operación en Ubuntu `:8080`, despliegue/rollback en Microsoft Azure y respaldos). |
| [INFORME_DE_CAMBIOS.md](INFORME_DE_CAMBIOS.md) | Historial técnico detallado de arquitectura, mejoras visuales, correcciones y fases de migración a WordPress/Azure. |
| [wordpress/deploy/UBUNTU_8080_QUICKSTART.md](wordpress/deploy/UBUNTU_8080_QUICKSTART.md) | Guía rápida de 1 página para instalar y actualizar WordPress en Linux Ubuntu (`:8080`). |
| [wordpress/deploy/AZURE_MIGRATION_RUNBOOK.md](wordpress/deploy/AZURE_MIGRATION_RUNBOOK.md) | Runbook de provisión, despliegue, sincronización de media a Blob Storage, staging slot y cutover en Microsoft Azure. |

---

## Inicio Rápido

### Opción A — Portal Base HTML/PHP (desarrollo local)

```bash
# Servidor completo con PHP (formularios, noticias :5001, panel admin)
php -d upload_max_filesize=12M -d post_max_size=25M -S localhost:8000
# Abrir: http://localhost:8000/index.html
```

Solo vista estática (sin PHP; carga desde `data/contenido.json`, `data/noticias.json` y `data/cumpleaneros.json`):
```bash
python3 -m http.server 8000
```

### Opción B — WordPress en Linux Ubuntu en el puerto `8080` (1 comando)

```bash
# 1. Diagnóstico rápido del servidor Ubuntu y conectividad :5001
./wordpress/scripts/check-network.sh

# 2. Instalar y levantar WordPress + MySQL 8 en :8080 (genera claves seguras en wordpress/.env)
./wordpress/scripts/install-ubuntu-8080.sh --url http://IP_DEL_SERVIDOR:8080 --open-firewall

# 3. Actualizar tras cada cambio subido a GitHub (sin tocar MySQL ni CVs privados)
./wordpress/scripts/update-ubuntu-8080.sh
```

### Opción C — Microsoft Azure (App Service Linux + MySQL Flexible Server + Blob Storage)

```bash
# 1. Configurar parámetros de tu suscripción (ignorado por Git)
cp wordpress/deploy/azure.env.example wordpress/deploy/azure.env
nano wordpress/deploy/azure.env

# 2. Aprovisionar infraestructura Azure (una sola vez tras 'az login')
./wordpress/deploy/azure-provision.sh

# 3. Desplegar o actualizar en 1 comando (con staging slot y swap sin caída)
./wordpress/scripts/deploy-azure.sh --pull --sync-media --slot staging
./wordpress/scripts/deploy-azure.sh --swap
```

---

## Módulos y Páginas del Portal

| Ruta (HTML/PHP) | Ruta (WordPress) | Descripción |
| :--- | :--- | :--- |
| `/index.html` | `/` | **Página Principal:** Barra inteligente con 3 logos institucionales, Hero rotativo, sección **Más que una simple empresa** con 4 pestañas y mascota institucional fija en columna lateral derecha, 2 productos destacados con Ficha Técnica, resumen de noticias, **Oportunidades Laborales (Envía tu CV)**, Clientes, **Carrusel Coverflow 3D** de 24 fotografías, FAQ, banda **OAC** y banda **Contáctanos Directamente**. |
| `/productos.html` | `/productos/` | **Catálogo Oficial 2025 (33 productos):** Productos de Fundición (2), Productos de Extrusión (24) y Productos de Laminación (7, incluido `#33 Rollo Cinta`), 9 fotografías recortadas de producto terminado, tarjetas compactas, buscador, modal de Ficha Técnica y formulario de cotización. |
| `/galeria.html` | `/galeria/` | **Galería Multimedia Institucional:** 4 videos completos de procesos, serie **Línea de Colada Continua en Operación** (9 fotos 4:5 en doble resolución) y 15 fotografías clasificadas en tarjetas horizontales compactas con visor **Lightbox 1:1 (Resolución Original)**. |
| `/prensa.html` | `/prensa/` | **Pulso Industrial CVG Alcasa:** Sala de Prensa pública paginada (páginas 1–72) sincronizada con `http://alcasa.com.ve:5001` con respaldo local automático y modal de vista previa. |
| `/minimal.html` | `/periodico/` | **Periódico Digital CVG Alcasa:** Vista editorial pública con búsqueda por texto/fecha/mes/año, paginación 1–72, lectura individual tipo tabloide (`?noticia=ID`) y modales legales. |
| `/sitio/Empresa/CA/index.html` | `/cumpleaneros/` | **Nuestros Cumpleañeros:** Directorio de cumpleaños del personal (3.543 trabajadores desde `DATA.csv`), filtro día/mes, foto o silueta por género, confeti y tarjeta descargable en PNG (810×1440) o PDF. |
| `/contactos.html` | `/wp-admin/` (`CVG Alcasa`) | **Panel Administrativo Unificado:** Gestión exclusiva para administradores (`admin` en PHP base; rol `manage_options` en WordPress) de **Contactos Comerciales** y **Postulaciones de CV** con descarga segura de archivos adjuntos y diagnóstico del sistema. |

---

## Seguridad y Protección de Datos Personales

- `data/cumpleaneros.json`, `storage/*.csv`, `storage/cv/`, `wordpress/.env`, `wordpress/deploy/azure.env` y `wordpress/dist/` están excluidos de Git (`.gitignore` y `wordpress/.gitignore`).
- En la versión WordPress (`cvg-alcasa-core`), los formularios se almacenan en **MySQL (`$wpdb`)**, las descargas de CV están protegidas fuera de `wwwroot` detrás de autenticación `manage_options` + nonces de WordPress, y se elimina cualquier contraseña embebida en código fuente.
- **Semillas versionadas:** `data/contactos-semilla.csv` y `data/postulaciones-semilla.csv` se conservan en `data/` para inicializar instalaciones limpias.
