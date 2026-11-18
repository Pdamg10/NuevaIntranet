# CVG Aluminio del Caroní, S.A. (CVG Alcasa) — Portal Institucional v2

Portal web institucional de **CVG Aluminio del Caroní, S.A. (CVG Alcasa)** desarrollado en HTML5, CSS3 y JavaScript modular con backend PHP (preparado para integración directa con WordPress).

El detalle técnico completo de la arquitectura y evolución del proyecto se encuentra en `INFORME_DE_CAMBIOS.md`.

---

## Ejecución en Entorno Local

### 1. Servidor completo con PHP (Formularios, Sala de Prensa Paginada, Periódico Digital, Galería, Contactos y Postulaciones de CV)
```bash
php -d upload_max_filesize=12M -d post_max_size=25M -S localhost:8000
# Abrir http://localhost:8000/index.html
```

### 2. Solo vista estática (sin PHP)
```bash
python3 -m http.server 8000
# Las noticias, el catálogo, la galería y los cumpleañeros cargan desde los respaldos locales
# data/noticias.json, data/contenido.json y data/cumpleaneros.json. Los formularios (contacto, CV),
# el panel administrativo y sus semillas de datos (data/*-semilla.csv) requieren PHP.
```

---

## Páginas del Proyecto

| Archivo | Descripción |
| :--- | :--- |
| `/index.html` | Portal principal (Barra superior inteligente con los 3 logos institucionales que se oculta al deslizar, Hero 3D, Empresa, Catálogo Destacado con Ficha Técnica flotante, Sala de Prensa con vista previa emergente, Oportunidades Laborales — Envía tu CV, Clientes, Galería de Procesos con paneles dashboard blancos, videos y vitrina destacada de **Colada Continua**, OAC y Contacto). |
| `/productos.html` | Catálogo Oficial de Productos 2025 completo (32 productos de Fundición, Extrusión y Laminación con ventana emergente flotante de Ficha Técnica detallada y descarga de PDF). |
| `/galeria.html` | **Galería Multimedia Institucional** — Sección dedicada con paneles dashboard blancos: 4 videos institucionales completos, serie fotográfica **Línea de Colada Continua en Operación** (9 fotografías verticales 4:5 con miniaturas optimizadas y visor Lightbox en resolución completa) y 15 fotografías clasificadas de *Planta de Laminación*, *Planta de Fundición*, *Planta de Extrusión* y *Compromiso Social*. |
| `/prensa.html` | **Pulso Industrial CVG Alcasa** — Sala de Prensa pública conectada a `http://alcasa.com.ve:5001/?page=1` (páginas 1 a 72), con carrusel automático en miniatura y ventana emergente flotante de **Vista rápida**. |
| `/minimal.html` | **Periódico Digital CVG Alcasa** — Vista pública de lectura de noticias con la estructura del portal `http://alcasa.com.ve:5001/` y el diseño corporativo de la intranet (`main.css` + *Inter* + Font Awesome): franja superior de fecha/ubicación, cabecera blanca con logo y «Edición N° 001», barra **Inicio · Portal**, búsqueda por texto/fecha/mes/año, paginación 1–72, vista individual tipo tabloide `?noticia=ID` con botones de compartir y descarga de imagen, y modales legales (*Aviso Legal*, *Política de Privacidad*, *Contacto*). |
| `/contactos.html` | **Panel de Contactos y Postulaciones (CV)** — Administración exclusiva del **Administrador General (`admin`)** para los mensajes del formulario de contacto (`storage/contactos.csv`) y las postulaciones del formulario *Envía tu Síntesis Curricular (CV)* (`storage/postulaciones.csv` + adjuntos en `storage/cv/`): barra superior azul, pestañas conmutables **Contactos Comerciales** y **Postulaciones de CV / Empleo**, filtros por interés/área, tarjetas con acciones **Ver · WA · Copiar · Eliminar** (marca local en `localStorage`) y **Ver · WA · Copiar · CV** (descarga directa del archivo adjunto), más modal de detalle. |
| `/sitio/Empresa/CA/index.html` | **Nuestros Cumpleañeros** — Módulo de cumpleaños del personal (3.543 trabajadores desde `DATA.csv`): filtro por día/mes, tarjetas con foto real o silueta por género, confeti y ventana emergente con la plantilla institucional (descarga en PNG/PDF). |

---

## Acceso Administrativo (`storage/usuarios_config.json`)

| Usuario | Rol / Área | Funciones Asignadas |
| :--- | :--- | :--- |
| `admin` | Administrador General CVG Alcasa | Acceso exclusivo al **Panel de Contactos y Postulaciones (CV)** (`/contactos.html`), con ambas pestañas (**Contactos Comerciales** y **Postulaciones de CV / Empleo**) y descarga de currículums adjuntos (`storage/cv/`). |

---

## Datos personales (importante)

`data/cumpleaneros.json` y las fotos de `assets/img/cumpleaneros/` contienen datos reales de trabajadores; los formularios generan `storage/*.csv` y `storage/cv/` con datos personales. El `.gitignore` excluye los archivos generados; **las semillas `data/contactos-semilla.csv` (copiada del servidor vivo `alcasa.com.ve`) y `data/postulaciones-semilla.csv` sí se versionan** para que el panel funcione en instalaciones limpias, y deben mantenerse a salvo si el repositorio es público.
