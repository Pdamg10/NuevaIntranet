# Informe Detallado de Cambios — Portal CVG Alcasa v2 (Rama `copia`)

## 1. Resumen Ejecutivo
Se fusionó el **sistema de diseño visual, identidad corporativa e interactividad de la rama `main`** (`Index.htm`, `components/header.html`, `sitio/Empresa/Noticias/Prensa.html`) dentro de la **arquitectura modular, limpia y orientada a PHP/WordPress de la rama `copia`**.

Con esto se eliminan los +27.000 archivos legacy de FrontPage (`_vti_cnf`, copias duplicadas, handlers `.ashx` en C#) manteniendo el 100% del diseño institucional de `main` y la estructura desacoplada de `copia`.

---

## 2. Estructura del Proyecto Conservada (`copia`)
```text
/
├── index.html                  # Portal principal con todas las secciones y diseño de main
├── prensa.html                 # Sala de Prensa dedicada con diseño de main + minicarátulas
├── INFORME_DE_CAMBIOS.md       # Documentación técnica de los cambios y ruta a WordPress
├── README.md                   # Guía rápida de ejecución local (PHP / estático)
├── web.config                  # Reglas de seguridad para IIS (bloqueo de storage/ y .csv)
├── .gitignore                  # Exclusión de datos sensibles generados (storage/*.csv, storage/cv/)
├── api/                        # Backend modular en PHP (preparado para WordPress)
│   ├── _lib.php                # Utilidades compartidas (sanitización CSV, JSON/FormData, correo)
│   ├── contacto.php            # Endpoint de recepción del formulario de contacto
│   ├── cv.php                  # Endpoint de recepción de Síntesis Curricular (PDF/DOC/DOCX)
│   └── noticias.php            # Proxy de Sala de Prensa con caché (120 s) y respaldo local
├── assets/
│   ├── css/
│   │   └── main.css            # Sistema de diseño unificado extraído de main
│   ├── js/
│   │   └── app.js              # Motor interactivo unificado (Header, Slider 3D, Tabs, API, BlurText, Traductor)
│   ├── img/                    # Imágenes institucionales extraídas de main:Imagenes/portal/
│   └── media/
│       └── catalogo-productos-2023.pdf
├── data/
│   ├── contenido.json          # Textos oficiales, fichas técnicas de los 8 productos, videos y contacto
│   └── noticias.json           # Respaldo local de noticias con minicarátula
└── storage/
    └── .htaccess               # Bloqueo de acceso web directo a archivos privados (Apache)
```

---

## 3. Detalle de Cambios Realizados por Módulo

### 3.1. Sistema de Diseño y Hoja de Estilos ([`assets/css/main.css`](file:///home/phame/Documentos/github/NuevaIntranet/assets/css/main.css))
- **Restauración de Variables Corporativas de `main`**: `--alcasa-blue` (`#003366`), `--alcasa-blue-dark` (`#002244`), `--alcasa-light-blue` (`#0056a3`), `--alcasa-accent` (`#ff6b1a`), `--alcasa-accent-hover` (`#e55a0f`), sombras y radios de borde.
- **Header Corporativo en 3 Zonas + Barra Superior de Idiomas (`.alcoa-langbar` y `.alcoa-header`)**:
  - Zona 1: Logo CVG Alcasa.
  - Zona 2: Menú principal `.jelly-menu` con submenús desplegables (`.dropdown-menu`), soporte para pantallas intermedias y *drawer* deslizante para móviles/tablets (`<= 992px`).
  - Zona 3: Logos de CVG y Gobierno Bolivariano.
  - Efecto de difuminado inferior dinámico en scroll (`.header-scroll-fade`) y botón flotante de WhatsApp (`.whatsapp-float`) con pulso animado y *tooltip*.
- **Hero 3D Slider & Glowing Cards (`.hero-3d-wrapper`)**:
  - Escenario 3D con perspectiva (`1400px`), auras animadas naranja/azul y 4 tarjetas interactivas (`.hero-card-blue`, `.hero-card-orange`, `.hero-card-green`, `.hero-card-gold`).
- **Sección Empresa (`.empresa-section`)**:
  - Diseño con barra superior degradada, navegación por 4 pestañas (`.empresa-tabs-nav`), tarjeta con mascota institucional (`mascota-alcasa.png`) y grillas de Valores y Pilares (`.valores-grid`).
- **Catálogo de Productos, Ficha Técnica Inline y FAQ**:
  - Barra de filtros por línea (`.product-filter-bar`), 8 tarjetas `.glowing-card-exact`, panel expandible inline sin popups (`.product-ficha-panel`) y acordeón de Preguntas Frecuentes (`.faq-box`).
- **Sala de Prensa con Minicarátula (`.prensa-api-card`)**:
  - Se combinó el diseño de tarjetas corporativas de `main` con la **minicarátula de portada** (`.prensa-api-thumb-wrap` + `.prensa-api-thumb`) de `copia`, manteniendo etiqueta de categoría, ID, fecha y enlace.
- **Oportunidades Laborales / Postulación de CV (`.oportunidades-section`)**:
  - Diseño a dos columnas con tarjeta informativa azul oscuro (`.oportunidades-info-card`) y formulario con zona de arrastre (`.cv-file-dropzone`).
- **Nuestros Clientes (`.clientes-section`) y Galería de Procesos (`#galeria`)**:
  - Tarjetas de testimonios con avatares y 6 tarjetas *glowing* para los procesos productivos con reproductor expandible inline (`#videoPlayerPanel`).
- **Footer Institucional, Caja OAC y Formulario de Contacto**:
  - Banner destacado del Sistema de Atención al Ciudadano (`.atencion-ciudadano-box`), formulario de contacto (`.contacto-form-wrapper`) y pie de página de 4 columnas (`.footer-grid`).

### 3.2. Estructura HTML ([`index.html`](file:///home/phame/Documentos/github/NuevaIntranet/index.html) y [`prensa.html`](file:///home/phame/Documentos/github/NuevaIntranet/prensa.html))
- **[`index.html`](file:///home/phame/Documentos/github/NuevaIntranet/index.html)**: Reemplaza el HTML minimalista temporal por las 8 secciones completas de `main:Index.htm`, pero limpio de CSS/JS inline (todo delegado a `/assets/css/main.css` y `/assets/js/app.js`) y apuntando a rutas organizadas bajo `/assets/img/` y `/assets/media/`.
- **[`prensa.html`](file:///home/phame/Documentos/github/NuevaIntranet/prensa.html)**: Conserva la estructura de `main:sitio/Empresa/Noticias/Prensa.html` (banner superior, contador en vivo, grilla de noticias con minicarátula, paginación y respaldo de iframe al periódico digital).

### 3.3. Motor JavaScript Unificado ([`assets/js/app.js`](file:///home/phame/Documentos/github/NuevaIntranet/assets/js/app.js))
- Mantiene el objeto de configuración `CFG` (`window.ALCASA_CFG`) para fácil integración con WordPress (`wp_localize_script`).
- Integra en un solo archivo modular:
  1. Renderizado automático del Header Corporativo (`#site-header`), Botón de WhatsApp y Footer (`#site-footer`).
  2. Animaciones `BlurText` y revelación por scroll (`.fold-reveal` con `IntersectionObserver`).
  3. Traductor multi-idioma persistente por cookies (`window.alcosaSetLanguage` + Google Translate).
  4. Desplazamiento suave determinístico (`smoothScrollTo`) sincronizado con pestañas (`data-tab-target`) y filtros de catálogo (`data-cat`).
  5. Lógica del Slider 3D del Hero (autoplay 6 s, pausa en hover, controles y swipe táctil).
  6. Fichas técnicas de los 8 productos (sincronizadas con `data/contenido.json`) y pre-llenado automático del formulario al pulsar **"Cotizar"**.
  7. Cliente de Sala de Prensa con soporte de minicarátula, paginación y triple nivel de respaldo (`/api/noticias.php` → `/data/noticias.json` → `iframe` del periódico digital).
  8. Envío asíncrono (`fetch` + `FormData`) de Contacto (`/api/contacto.php`) y Postulación de CV con archivo adjunto (`/api/cv.php`).

### 3.4. Datos y Recursos (`data/`, `assets/img/`, `assets/media/`)
- Se extrajeron desde la rama `main` las 21 imágenes del portal hacia `assets/img/` y el catálogo PDF hacia `assets/media/catalogo-productos-2023.pdf`.
- Se completó [`data/contenido.json`](file:///home/phame/Documentos/github/NuevaIntranet/data/contenido.json) eliminando los textos `"PENDIENTE"` y registrando la Misión, Visión, Valores, Pilares, los 8 Productos con sus fichas técnicas y los 6 Videos de Galería.
- Se actualizaron las noticias de respaldo en [`data/noticias.json`](file:///home/phame/Documentos/github/NuevaIntranet/data/noticias.json) con minicarátulas locales.

### 3.5. Backend PHP (`api/`)
- **[`api/_lib.php`](file:///home/phame/Documentos/github/NuevaIntranet/api/_lib.php)**: Se añadió `input_data()` para aceptar tanto envíos `multipart/form-data` (`FormData`) como `application/json`, manteniendo la sanitización anti-CSV injection (`clean()`) y bloqueo de archivo (`flock`).
- **[`api/contacto.php`](file:///home/phame/Documentos/github/NuevaIntranet/api/contacto.php)**: Procesa el formulario de contacto y almacena en `storage/contactos.csv`.
- **[`api/cv.php`](file:///home/phame/Documentos/github/NuevaIntranet/api/cv.php)**: Valida campos y archivo adjunto (`.pdf`, `.doc`, `.docx` hasta 10 MB por extensión y MIME real con `finfo`), guarda con nombre aleatorio seguro en `storage/cv/` y registra la postulación en `storage/postulaciones.csv`.
- **[`api/noticias.php`](file:///home/phame/Documentos/github/NuevaIntranet/api/noticias.php)**: Consume `http://alcasa.com.ve:5001/api/noticias`, normaliza campos (`id`, `categoria`, `titulo`, `resumen`, `imagen`, `fecha`, `url`), guarda caché de 120 segundos y usa `data/noticias.json` si el servidor del periódico no responde.
