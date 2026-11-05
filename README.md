# CVG Aluminio del Caroní, S.A. (CVG Alcasa) — Portal Institucional v2

Portal web institucional de **CVG Aluminio del Caroní, S.A. (CVG Alcasa)** desarrollado en HTML5, CSS3 y JavaScript modular con backend PHP (preparado para integración directa con WordPress).

El detalle técnico completo de la arquitectura y evolución del proyecto se encuentra en `INFORME_DE_CAMBIOS.md`.

---

## Ejecución en Entorno Local

### 1. Servidor completo con PHP (Formularios, Sala de Prensa Paginada, Galería, Redacción, Contactos y Postulaciones de CV)
```bash
php -d upload_max_filesize=12M -d post_max_size=25M -S localhost:8000
# Abrir http://localhost:8000/index.html
```

### 2. Solo vista estática (sin PHP)
```bash
python3 -m http.server 8000
# Las noticias, el catálogo y los cumpleañeros cargan desde los respaldos locales data/noticias.json,
# data/contenido.json y data/cumpleaneros.json. Los formularios (contacto, CV), los paneles
# administrativos y sus semillas de datos (data/*-semilla.csv) requieren PHP.
```

---

## Páginas del Proyecto

| Archivo | Descripción |
| :--- | :--- |
| `/index.html` | Portal principal (Barra superior inteligente con los 3 logos institucionales que se oculta al deslizar, Hero 3D, Empresa, Catálogo Destacado con Ficha Técnica flotante, Sala de Prensa con vista previa emergente, Oportunidades Laborales — Envía tu CV, Clientes, Galería Multimedia con miniaturas GIF animadas, OAC y Contacto). |
| `/productos.html` | Catálogo Oficial de Productos 2025 completo (32 productos de Fundición, Extrusión y Laminación con ventana emergente flotante de Ficha Técnica detallada y descarga de PDF). |
| `/galeria.html` | **Galería Multimedia Institucional** — Sección dedicada con todos los videos industriales completos (`.mp4` con previsualización `.gif`) y fotografías clasificadas por área (*Fundición*, *Extrusión*, *Laminación*, *Planta y Operaciones*, *Gestión Social*), buscador y reproductor/visor emergente. |
| `/prensa.html` | **Pulso Industrial CVG Alcasa** — Sala de Prensa pública (sin barra de login administrativo) conectada a `http://alcasa.com.ve:5001/?page=1` (páginas 1 a 72), con carrusel automático en miniatura y ventana emergente flotante de **Vista rápida**. |
| `/redaccion.html` | **Sala de Redacción (Intranet Independiente)** — Aplicación administrativa separada del portal público (sin menú principal) con diseño en 3 zonas para crear/editar noticias, carrusel de hasta 20 fotos por noticia, borradores e historial de versiones. |
| `/contactos.html` | **Panel de Contactos (exclusivo)** — Administración de los mensajes del formulario de contacto, con la apariencia del sitio vivo adaptada al diseño de la rama: login «Acceso Restringido» a pantalla completa (solo para `admin` y `comercializacion`), barra superior azul, filtro con la lista fija de intereses, cuadrícula de tarjetas con acciones **Ver · WA · Copiar · Eliminar** (Eliminar marca la tarjeta localmente, con persistencia en `localStorage`) y modal de detalle con copia para WhatsApp/Correo. Sin bandeja de CV. |
| `/cv.html` | **Panel de Postulaciones (CV) (exclusivo)** — Administración de las postulaciones del formulario *Envía tu Síntesis Curricular (CV)*, con el mismo lenguaje visual del panel de contactos: login «Acceso Restringido» a pantalla completa (solo para `admin` y `talento`), barra superior azul, filtro con la lista fija de áreas, cuadrícula de tarjetas con C.I., nivel académico y acciones **Ver · WA · Copiar · CV** (descarga el PDF adjunto, sin escritura en el servidor) y modal de detalle con copia para WhatsApp/Correo. |
| `/sitio/Empresa/CA/index.html` | **Nuestros Cumpleañeros** — Página del módulo de cumpleaños del personal (3.543 trabajadores desde `DATA.csv`): filtro por día/mes, tarjetas con foto real o silueta por género, confeti y ventana emergente con la plantilla institucional (descarga en PNG/PDF). |
| `/minimal.html` | **Periódico Digital CVG Alcasa (réplica)** — Copia fiel del portal «información institucional» de `http://alcasa.com.ve:5001/` (tipografía serif, franja superior con fecha de edición, cabecera con logo y «Edición N° 001», barra nav «Inicio / Iniciar Sesión», buscador con fecha/mes/año, paginación compacta 1–72, tarjetas **noticias-grid**, botones flotantes ⬆⬇ y pie con Aviso Legal/Privacidad/Contacto). Consumida de `api/noticias.php` con respaldo directo al portal vivo. Login integrado vía `api/prensa_admin.php` (`comunicacion` / `periodico2024`): con sesión muestra «Publicar Nueva Noticia» (→ `/redaccion.html`), «Cerrar Sesión» y la barra **✏️ Editar (Redacción)** en cada tarjeta. |

---

## Niveles de Acceso y Roles Exclusivos (`storage/usuarios_config.json`)

| Nivel | Usuario | Rol / Área | Funciones Asignadas |
| :--- | :--- | :--- | :--- |
| **Nivel 1** | `admin` | Super Administrador General | Control total: **Redacción de Sala de Prensa** (`/redaccion.html`, incluyendo eliminación permanente de noticias) + **Panel de Contactos** (`/contactos.html`) + **Panel de Postulaciones (CV)** (`/cv.html`). |
| **Nivel 2** | `comunicacion` | Gerencia de Comunicación Estratégica | Acceso exclusivo a **Redacción de Sala de Prensa** (`/redaccion.html`): redactar, subir hasta 20 imágenes al carrusel, guardar borradores, archivar y publicar noticias. |
| **Nivel 3** | `comercializacion` | Gerencia de Comercialización | Acceso exclusivo al **Panel de Contactos** (`/contactos.html`), dedicado únicamente a los mensajes del formulario de contacto. |
| **Nivel 4** | `talento` | Gerencia de Personal / Talento Humano | Acceso exclusivo al **Panel de Postulaciones (CV)** (`/cv.html`), dedicado únicamente a las postulaciones del formulario *Envía tu Síntesis Curricular (CV)* (`storage/postulaciones.csv`, PDF en `storage/cv/`). |

---

## Datos personales (importante)

`data/cumpleaneros.json` y las fotos de `assets/img/cumpleaneros/` contienen datos reales de trabajadores; los formularios generan `storage/*.csv` con datos personales. El `.gitignore` excluye los CSV generados; **las semillas `data/contactos-semilla.csv` (copiada del servidor vivo `alcasa.com.ve`) y `data/postulaciones-semilla.csv` sí se versionan** para que los paneles funcionen en instalaciones limpias, y deben mantenerse a salvo si el repositorio es público.
