# Informe Detallado de Cambios — Portal CVG Alcasa v2 (Rama `copia`)

## 1. Resumen Ejecutivo
Se consolidó el **Portal Institucional v2 de CVG Aluminio del Caroní, S.A. (CVG Alcasa)** en la rama `copia`, integrando una experiencia interactiva moderna (ventanas emergentes flotantes para fichas técnicas, noticias, videos y fotografías; barra de navegación inteligente que se oculta al deslizar; galería multimedia dedicada con previsualizaciones en GIF; y conexión paginada con el servidor de noticias `:5001`), junto con paneles administrativos independientes por nivel de usuario.

---

## 2. Estructura Actual del Proyecto
```text
/
├── index.html                  # Portal principal institucional
├── productos.html              # Catálogo Oficial 2025 completo (32 productos con Ficha Técnica flotante)
├── galeria.html                # Galería Multimedia dedicada (Videos completos + Fotografías clasificadas)
├── prensa.html                 # Sala de Prensa "Pulso Industrial CVG Alcasa" (Paginación :5001 + Modal de vista previa)
├── redaccion.html              # Aplicación administrativa independiente de Redacción (3 zonas, hasta 20 fotos)
├── contactos.html              # Aplicación administrativa independiente de Contactos Comerciales y Postulaciones CV
├── minimal.html                # Vista compacta de noticias
├── favicon.ico                 # Favicon multirresolución con el logo metálico de CVG Alcasa
├── INFORME_DE_CAMBIOS.md       # Documentación técnica detallada de cambios
├── README.md                   # Guía de inicio rápido y matriz de niveles de acceso
├── web.config                  # Reglas de seguridad para IIS
├── .gitignore                  # Exclusión de datos personales generados (storage/*.csv, storage/cv/)
├── api/                        # Backend modular en PHP
│   ├── _lib.php                # Utilidades compartidas (sanitización CSV, JSON/FormData, correo)
│   ├── admin_datos.php         # API protegida por rol para Contactos Comerciales y Postulaciones CV
│   ├── contacto.php            # Recepción de formulario de contacto comercial (storage/contactos.csv)
│   ├── cv.php                  # Recepción de Síntesis Curricular (PDF/DOC/DOCX) y registro estructurado
│   ├── noticias.php            # Feed paginado de noticias (locales + servidor http://alcasa.com.ve:5001/?page=N)
│   └── prensa_admin.php        # Autenticación por niveles, CRUD de noticias, carrusel (20 fotos) e historial
├── assets/
│   ├── css/
│   │   └── main.css            # Sistema de diseño corporativo unificado y modales flotantes de alto contraste
│   ├── js/
│   │   └── app.js              # Motor interactivo del portal (catálogo, galería, noticias, modales, header scroll)
│   ├── img/
│   │   ├── galeria/            # Miniaturas animadas (.gif) para los videos industriales
│   │   └── ...                 # Logos institucionales, favicon.png, fotografías de plantas, noticias y 32 productos
│   └── media/
│       ├── videos/             # Videos institucionales completos en MP4 (Laminación, Fundición, Extrusión, Gestión Social)
│       └── ...                 # Catálogos PDF oficiales (2023 y 2025)
├── data/
│   ├── contenido.json          # Datos institucionales, 32 productos con fichas técnicas, galería multimedia y FAQ
│   └── noticias.json           # Almacén y respaldo local con las 50 noticias más recientes del servidor :5001
└── storage/
    ├── .htaccess               # Bloqueo de acceso web directo a archivos privados
    └── usuarios_config.json    # Configuración de los 4 niveles de usuarios y permisos exclusivos
```

---

## 3. Detalle de Cambios Implementados

### 3.1. Ventanas Emergentes Flotantes Centradas y Botones de Alto Contraste (`assets/js/app.js`, `assets/css/main.css`, `index.html`, `productos.html`, `prensa.html`, `galeria.html`)
- **Posicionamiento fijo real en pantalla (`document.body`):**
  - Se corrigió el problema donde las ventanas emergentes aparecían desplazadas arriba o abajo por estar dentro de secciones con `transform: translateY(0)` (`.fold-reveal`).
  - Ahora `initProductosCatalogo()`, `initGaleriaVideos()`, `initPaginaGaleria()` e `initNoticias()` adjuntan automáticamente `#productFichaPanel`, `#videoPlayerPanel`, `#galeriaLightboxModal` y `#noticiaDetailPanel` directamente a `document.body` con `.ficha-modal-backdrop` (`position: fixed !important; inset: 0; width: 100vw; height: 100vh; z-index: 99999`).
- **Ficha Técnica de Productos en Ventana Flotante (`#productFichaPanel`):**
  - Muestra la imagen del producto, código de catálogo, categoría, aleaciones y temples disponibles, dimensiones/especificaciones, aplicaciones industriales, barra de composición química y acciones directas.
- **Vista Previa de Noticias en Ventana Flotante (`#noticiaDetailPanel`):**
  - Tanto en `index.html` como en `prensa.html`, al hacer clic en cualquier tarjeta de noticia o en el botón **Vista rápida**, se abre una ventana emergente centrada (`.noticia-modal-dialog`) con la fotografía o galería interactiva, categoría, fecha, autor, resumen ejecutivo, cuerpo completo y enlace a la fuente original.
- **Contraste de Botones Mejorado a Simple Vista:**
  - Se crearon y aplicaron clases con alto contraste cromático: `.btn-primary-corp` (azul institucional con texto blanco), `.btn-modal-secondary` (azul marino `#13325b` con borde celeste `#60a5fa` y texto blanco), `.btn-modal-close` (gris pizarra `#334155` con texto blanco y hover rojo `#dc2626`), `.btn-cta-banner-white` (fondo blanco puro con letras azul marino `#002b59`) y `.btn-prensa-quickview-sm` (azul oscuro `#0f294a` con texto blanco).

### 3.2. Barra de Navegación Inteligente y Visibilidad Permanente de Logos (`assets/js/app.js`, `assets/css/main.css`)
- **Ocultamiento suave al deslizar:** En `initStickyHeader()`, la barra superior (`#site-header`) se oculta suavemente hacia arriba (`header-hidden`) cuando el usuario desliza hacia abajo para dar mayor espacio de lectura, y reaparece de inmediato al deslizar hacia arriba o volver al inicio.
- **3 Logos Institucionales Siempre Visibles:** Mientras la barra de menú está en pantalla, los tres emblemas oficiales (`logo-mpibn.jpg`, `logo-cvg.png` y `logo-alcasa.png`) permanecen siempre visibles y nítidos.
- **Navegación Directa a Galería:** Al hacer clic en la opción **Galería** del menú principal, el usuario es dirigido a la sección dedicada `/galeria.html`.

### 3.3. Sala de Prensa Pública Conectada a `http://alcasa.com.ve:5001/?page=1` (`prensa.html`, `api/noticias.php`, `data/noticias.json`)
- **Eliminación del Inicio de Sesión en Sala de Prensa:** Se retiró por completo el bloque de acceso administrativo de `prensa.html` para que la gestión editorial se maneje exclusivamente desde su página independiente (`/redaccion.html`).
- **Conexión y Paginación con el Servidor `:5001`:**
  - `api/noticias.php` acepta el parámetro `?page=N` (páginas 1 a 72), consulta en tiempo real `http://alcasa.com.ve:5001/?page=N`, extrae titulares, fechas, enlaces, resúmenes e imágenes reales (`/media/noticias/...`) y cuenta con respaldo automático en `data/noticias.json` (actualizado con las 50 noticias más recientes del portal `:5001`).
  - `prensa.html` incorpora controles de paginación (`#prensaPaginationBar`) para recorrer las páginas del archivo histórico.

### 3.4. Galería Multimedia Institucional con Miniaturas GIF y Página Dedicada (`index.html`, `galeria.html`, `data/contenido.json`)
- **Depuración de Tarjetas:** Se eliminaron los ítems `"CVG Alcasa Hoy — Hitos y Logros Productivos"` e `"Integración de Empresas Tuteladas de la CVG"`.
- **Tarjetas Estilo Sala de Prensa con Miniaturas Animadas (`.gif`):**
  - Se generaron 4 videos institucionales completos en MP4 (`assets/media/videos/video-laminacion.mp4`, `video-fundicion.mp4`, `video-extrusion.mp4`, `video-ayudas-tecnicas.mp4`) y sus respectivas miniaturas animadas en GIF (`assets/img/galeria/gif-laminacion.gif`, `gif-fundicion.gif`, `gif-extrusion.gif`, `gif-ayudas.gif`).
- **Nueva Página `/galeria.html`:**
  - Incluye filtros por tipo (*Todos*, *Videos Institucionales*, *Fotografías*) y por categoría industrial (*Laminación*, *Fundición*, *Extrusión*, *Planta y Operaciones*, *Gestión Social*), buscador en tiempo real, reproductor emergente de video completo y visor Lightbox con navegación entre fotografías.

### 3.5. Independencia Visual de los Paneles Administrativos y Limpieza de Imágenes (`redaccion.html`, `contactos.html`, `assets/css/main.css`)
- **Páginas Administrativas Separadas:** En `redaccion.html` y `contactos.html` se eliminó la barra del menú principal del portal público y el subtítulo `"Estructura del panel de edición de noticias · CVG Aluminio del Caroní, S.A. (CVG Alcasa)"`, rediseñando su cabecera superior (`admin-workspace-topbar`) y botones con estilo propio de intranet administrativa.
- **Eliminación de Líneas Cuadriculadas sobre Imágenes:** Se eliminaron los fondos CSS `repeating-linear-gradient` en contenedores de imágenes y tarjetas para evitar que aparezcan tramas o líneas cuadriculadas durante la carga o visualización de fotografías.

### 3.6. Separación de los 4 Niveles de Usuario y Formulario de CV (`storage/usuarios_config.json`, `api/cv.php`)
- **Nivel 1 (`admin`):** Control total de Redacción (incluyendo eliminación permanente) + Bandejas de Contactos Comerciales y Postulaciones CV.
- **Nivel 2 (`comunicacion`):** Acceso exclusivo a Redacción de Sala de Prensa (`/redaccion.html`).
- **Nivel 3 (`comercializacion`):** Acceso exclusivo a la Bandeja de Contactos Comerciales en `/contactos.html`.
- **Nivel 4 (`talento`):** Acceso exclusivo a la Bandeja de Postulaciones de Síntesis Curricular (CV) en `/contactos.html`, conectada directamente al formulario único **Envía tu Síntesis Curricular (CV)** de `index.html`.
