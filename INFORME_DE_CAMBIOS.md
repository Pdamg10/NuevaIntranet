# Informe Detallado de Cambios — Portal CVG Alcasa v2 (Rama `copia`)

## 1. Resumen Ejecutivo
Se consolidó el **Portal Institucional v2 de CVG Aluminio del Caroní, S.A. (CVG Alcasa)** en la rama `copia`, integrando una experiencia interactiva moderna (ventanas emergentes flotantes para fichas técnicas, noticias, videos y fotografías; barra de navegación inteligente que se oculta al deslizar; galería multimedia con paneles dashboard blancos y serie fotográfica dedicada de **Colada Continua**; y consulta paginada de noticias conectada al servidor `:5001` en `prensa.html` y `minimal.html`), el módulo **«Nuestros Cumpleañeros»** con las fotos reales del personal (silueta por género cuando no hay retrato y tarjeta de felicitación ampliada), y el **Panel Unificado de Contactos Comerciales y Postulaciones (CV)** (`/contactos.html`) para el Administrador General (`admin`).

---

## 2. Estructura Actual del Proyecto
```text
/
├── index.html                    # Portal principal institucional (mascota fija en Empresa, carrusel Coverflow de 24 fotos y bandas OAC/Contacto)
├── productos.html                # Catálogo Oficial 2025 completo (33 productos con Ficha Técnica flotante)
├── galeria.html                  # Galería Multimedia dedicada (Videos + Serie Colada Continua + Fotografías clasificadas)
├── prensa.html                   # Sala de Prensa "Pulso Industrial CVG Alcasa" (Paginación :5001 + Modal de vista previa)
├── minimal.html                  # Periódico Digital CVG Alcasa (visualización pública de noticias con el diseño de la intranet)
├── contactos.html                # Panel unificado de Contactos Comerciales y Postulaciones (CV) para el Administrador General
├── sitio/Empresa/CA/index.html   # Página «Nuestros Cumpleañeros» (filtro día/mes, tarjetas con silueta y plantilla)
├── tools/generar_cumpleaneros.py # Genera data/cumpleaneros.json y las fotos desde DATA.csv + BMP
├── favicon.ico                   # Favicon multirresolución con el logo metálico de CVG Alcasa
├── INFORME_DE_CAMBIOS.md         # Documentación técnica detallada de cambios
├── README.md                     # Guía de inicio rápido y páginas del portal
├── web.config                    # Reglas de seguridad para IIS
├── .gitignore                    # Exclusión de datos personales generados (storage/*.csv, storage/cv/)
├── api/                          # Backend modular en PHP
│   ├── _lib.php                  # Utilidades compartidas (sanitización CSV, JSON/FormData, semillas, correo)
│   ├── admin_datos.php           # API para el Administrador General: contactos, postulaciones y descarga de CV
│   ├── contacto.php              # Recepción de formulario de contacto comercial (storage/contactos.csv)
│   ├── cv.php                    # Recepción de Síntesis Curricular (PDF/DOC/DOCX) y registro en storage/postulaciones.csv
│   ├── noticias.php              # Feed paginado (?page=N) y detalle individual (?id=N) desde http://alcasa.com.ve:5001
│   └── prensa_admin.php          # Servicio backend de sesión y cuentas autorizadas del panel administrativo
├── assets/
│   ├── css/
│   │   ├── cumpleanos.css        # Estilos de las fichas de cumpleañeros (reutiliza variables de main.css)
│   │   └── main.css              # Sistema de diseño corporativo, paneles dashboard de galería y modales de alto contraste
│   ├── js/
│   │   ├── cumpleanos.js         # Renderiza las fichas desde data/cumpleaneros.json
│   │   └── app.js                # Motor interactivo del portal (catálogo, galería, lightbox, noticias, formularios, header)
│   ├── img/
│   │   ├── galeria/
│   │   │   ├── colada-continua/  # 9 fotografías originales (1080×1350) + thumbs/ (miniaturas 600×750)
│   │   │   └── ...               # Fotografías de plantas y previsualizaciones animadas de videos
│   │   ├── cumpleaneros/         # Retratos reales del personal y siluetas por género
│   │   ├── productos/            # 9 fotografías recortadas de producto terminado (*-ficha.jpg)
│   │   └── ...                   # Logos institucionales, mascota institucional, favicon.png, noticias y 33 productos
│   └── media/
│       ├── videos/               # Videos institucionales completos en MP4 (Laminación, Fundición, Extrusión, Ayudas Técnicas)
│       └── ...                   # Catálogos PDF oficiales (2023 y 2025)
├── data/
│   ├── contactos-semilla.csv     # Semilla versionada de la bandeja de contactos (8 registros del servidor vivo alcasa.com.ve)
│   ├── postulaciones-semilla.csv # Semilla versionada de la bandeja de postulaciones de CV (encabezados)
│   ├── cumpleaneros.json         # Cumpleañeros (3.543 trabajadores, generado desde DATA.csv)
│   ├── contenido.json            # Datos institucionales, 33 productos, galería de videos, Colada Continua, fotos y FAQ
│   └── noticias.json             # Respaldo local con las noticias más recientes del servidor :5001
└── storage/
    ├── .htaccess                 # Bloqueo de acceso web directo a archivos privados
    └── usuarios_config.json      # Configuración de la cuenta del Administrador General (admin)
```

---

## 3. Detalle de Cambios Implementados

### 3.1. Ventanas Emergentes Flotantes Centradas y Botones de Alto Contraste (`assets/js/app.js`, `assets/css/main.css`, `index.html`, `productos.html`, `prensa.html`, `galeria.html`)
- **Posicionamiento fijo real en pantalla (`document.body`):**
  - Se corrigió el desplazamiento de ventanas emergentes dentro de secciones con `transform: translateY(0)` (`.fold-reveal`), adjuntando `#productFichaPanel`, `#videoPlayerPanel`, `#galeriaLightboxModal` y `#noticiaDetailPanel` directamente a `document.body` con `.ficha-modal-backdrop` (`position: fixed !important; inset: 0; width: 100vw; height: 100vh; z-index: 99999`).
- **Ficha Técnica de Productos en Ventana Flotante (`#productFichaPanel`):**
  - Muestra imagen del producto, código de catálogo, categoría, aleaciones y temples disponibles, dimensiones/especificaciones, aplicaciones industriales, barra de composición química y acciones directas.
- **Vista Previa de Noticias en Ventana Flotante (`#noticiaDetailPanel`):**
  - Tanto en `index.html` como en `prensa.html`, al hacer clic en cualquier tarjeta de noticia o en **Vista rápida**, se abre una ventana emergente centrada (`.noticia-modal-dialog`) con fotografía o galería interactiva, categoría, fecha, resumen, cuerpo completo y enlace a la publicación oficial.
- **Contraste de Botones Mejorado:**
  - Se definieron clases de alto contraste cromático para botones primarios, secundarios, de cierre y acciones sobre fondos claros y oscuros.

### 3.2. Barra de Navegación Inteligente y Visibilidad Permanente de Logos (`assets/js/app.js`, `assets/css/main.css`)
- **Ocultamiento suave al deslizar:** En `initStickyHeader()`, la barra superior (`#site-header`) se oculta suavemente al desplazarse hacia abajo y reaparece de inmediato al subir o volver al inicio.
- **3 Logos Institucionales Siempre Visibles:** Los emblemas oficiales (`logo-institucional.png`, `logo-cvg.png` y `logo-alcasa.png`) se mantienen siempre visibles y nítidos en la cabecera.
- **Navegación Directa a Galería:** El enlace **Galería** del menú principal dirige a `/galeria.html`.

### 3.3. Sala de Prensa Pública Conectada a `http://alcasa.com.ve:5001/?page=1` (`prensa.html`, `api/noticias.php`, `data/noticias.json`)
- **Vista Pública de Noticias:** `prensa.html` presenta únicamente la consulta pública de noticias oficiales sin elementos administrativos.
- **Conexión y Paginación con el Servidor `:5001`:**
  - `api/noticias.php` acepta `?page=N` (páginas 1 a 72), consulta en tiempo real `http://alcasa.com.ve:5001/?page=N`, extrae titulares, fechas, enlaces, resúmenes e imágenes y cuenta con respaldo automático en `data/noticias.json`.
  - `prensa.html` incorpora controles de paginación (`#prensaPaginationBar`) para recorrer el archivo histórico.

### 3.4. Galería Multimedia Institucional y Página Dedicada (`index.html`, `galeria.html`, `data/contenido.json`)
- **Depuración de Tarjetas:** Se eliminaron los ítems `"CVG Alcasa Hoy — Hitos y Logros Productivos"` e `"Integración de Empresas Tuteladas de la CVG"`.
- **Videos Institucionales de Procesos:** Se integraron 4 videos completos en `assets/media/videos/` (*Planta de Laminación en Frío y Caliente*, *Planta de Fundición y Hornos de Fusión*, *Planta de Extrusión y Prensas de Perfilería* y *Taller Soberano de Ayudas Técnicas*) con previsualización animada y reproductor emergente.
- **Página `/galeria.html`:** Centraliza los videos institucionales, la serie fotográfica de Colada Continua y el registro fotográfico clasificado de las plantas y compromiso social con visor Lightbox.

### 3.5. Limpieza de Imágenes y Panel Independiente (`contactos.html`, `assets/css/main.css`)
- **Cabecera Independiente en `contactos.html`:** El panel administrativo cuenta con barra superior azul propia (`.adm-topbar`), separada del menú público del portal.
- **Eliminación de Líneas Cuadriculadas sobre Imágenes:** Se eliminaron los fondos CSS `repeating-linear-gradient` en contenedores de imágenes y tarjetas para evitar tramas durante la carga o visualización.

### 3.6. Paleta Azul Institucional y Acento Acero (`assets/css/main.css`, todas las páginas HTML y `assets/js/app.js`)
- **Paleta Azul Institucional:** Basada en el portal corporativo:
  - Azul principal: `#367EC6` (`--alcasa-blue` / `--alcoa-blue`).
  - Azul oscuro: `#236EB8` (`--alcasa-blue-dark`).
  - Azul claro de apoyo: `#5B9BD5` (`--alcasa-light-blue`).
  - Azul profundo complementario: `#054A91` y `#002285`.
- **Variable de Acento Acero (inspirada en el logo metálico de CVG Alcasa):** `--alcasa-accent: #4F6B80`, `--alcasa-accent-hover: #3F5668` y `--alcasa-accent-light: #C7D1DA` (reemplazando el naranja anterior en variables, gradientes y estilos).
- **Ventanas Flotantes en Tema Claro:** Fondo blanco del diálogo, cabecera con degradado azul (`#054A91 → #236EB8 → #367EC6`), acentos en acero claro y botones con alto contraste.

### 3.7. Página «Nuestros Cumpleañeros» (`sitio/Empresa/CA/index.html`, `data/cumpleaneros.json`, `assets/js/cumpleanos.js`, `assets/css/cumpleanos.css`, `tools/generar_cumpleaneros.py`)
- **Integración con la estructura actual:** Reescrita con `#site-header`, `#site-footer`, `main.css` compartido y `content-wrapper`, conservando el cuadro de felicitación, confeti (`canvas-confetti`), filtro día/mes y descarga de la tarjeta en PNG (810×1440) o PDF.
- **Base de datos y fotografías:** 3.543 trabajadores en `data/cumpleaneros.json`, con 3.355 retratos reales en `assets/img/cumpleaneros/` y siluetas por género (`silueta_masculina.png` / `silueta_femenina.png`) para los 188 trabajadores sin foto.
- **Depuración de imágenes vacías y ampliación de plantilla:** Se eliminaron 67 imágenes con «X» provenientes del respaldo antiguo, se ajustó `tools/generar_cumpleaneros.py` para filtrarlas automáticamente y se amplió la vista emergente de la plantilla a 480 px manteniendo la proporción 9:16.

### 3.8. Menú «Extranet» (`assets/js/app.js`, `index.html`)
- Alineado con la estructura corporativa en ambos puntos de renderizado (header estático de `index.html` y header inyectado por `assets/js/app.js`): 1) Consulta clave ARI · 2) Nuestros Cumpleañeros (`/sitio/Empresa/CA/index.html`) · 3) Acceso a la mensajería institucional (`alcasa.com.ve:5002`) · 4) Panel de Contactos (`/contactos.html`).

### 3.9. Panel Unificado de Contactos Comerciales y Postulaciones de CV (`contactos.html`, `api/contacto.php`, `api/cv.php`, `api/admin_datos.php`, `api/_lib.php`, `data/*-semilla.csv`)
- **Módulo unificado para el Administrador General (`admin`):** `contactos.html` concentra en una sola vista con pestañas conmutables tanto **Contactos Comerciales** (`storage/contactos.csv`) como **Postulaciones de CV / Empleo** (`storage/postulaciones.csv` + adjuntos en `storage/cv/`).
- **Gestión exclusiva de `admin`:** Se retiraron las cuentas adicionales (`comercializacion`, `talento` y `comunicacion`) de `storage/usuarios_config.json`, `api/prensa_admin.php` y `api/admin_datos.php`, así como todo enlace o referencia a Redacción dentro del panel.
- **Bandeja de Contactos Comerciales:** Filtros por búsqueda e intereses fijos (*Cotización, Fundición, Laminación, Extrusión, Comercialización, Catálogo de productos, Otro*), tarjetas con acciones **Ver · WA · Copiar · Eliminar** (marca local persistida en `localStorage`) y modal de detalle.
- **Bandeja de Postulaciones de CV / Empleo:** Conectada de extremo a extremo con el formulario *Envía tu Síntesis Curricular (CV)* de `index.html` vía `api/cv.php` (admite PDF, DOC y DOCX hasta 10 MB con validación de tamaño, extensión y tipo MIME). Filtros por búsqueda y las 7 áreas de postulación, tarjetas con acciones **Ver · WA · Copiar · CV** (descarga directa del archivo adjunto vía `api/admin_datos.php?tipo=descargar_cv`) y modal de detalle.
- **Semillas versionadas (`data/contactos-semilla.csv` y `data/postulaciones-semilla.csv`):** `leerCsvDeFuente()` sirve las semillas en instalaciones limpias y `csv_append()` en `api/_lib.php` las pre-copia al crearse `storage/*.csv` por primera vez, conservando los 8 registros históricos iniciales de contactos.

### 3.10. Periódico Digital de Consulta Pública y Retiro de Redacción (`minimal.html`, `api/noticias.php`)
- **Retiro del módulo de edición:** Se eliminó `redaccion.html` del proyecto junto con todas las referencias, botones y redirecciones asociadas en `minimal.html` y `contactos.html`.
- **Periódico Digital (`minimal.html`) solo para visualización de noticias con el nuevo diseño corporativo:**
  - Mantiene íntegramente el estilo configurado con `main.css`, *Inter* y Font Awesome: franja superior degradada (`.pd-top`), cabecera editorial blanca con logo y «Edición N° 001» (`.pd-cabecera`), barra de navegación simplificada (**Inicio · Portal**), tarjeta de filtros (búsqueda por texto, fecha, mes y año), paginación en píldoras (`1` a `72`), cuadrícula de tarjetas con borde azul e insignia acero, y modales legales (*Aviso Legal*, *Política de Privacidad*, *Contacto*).
  - **Vista individual de noticia (`?noticia=ID` + `api/noticias.php?id=N`):** Muestra titular, metadatos, botones para compartir, imagen principal con opciones **Ver** / **Descargar**, cuerpo completo del comunicado y navegación de retorno con soporte `pushState`/`popstate`.

### 3.11. Serie Fotográfica «Línea de Colada Continua», Doble Resolución y Paneles Dashboard en Galería (`assets/img/galeria/colada-continua/`, `galeria.html`, `index.html`, `data/contenido.json`, `assets/js/app.js`, `assets/css/main.css`)
- **Integración de 9 fotografías de Colada Continua en doble resolución:**
  - **Originales intactos (`1080 × 1350 px`, proporción 4:5)** almacenados en `assets/img/galeria/colada-continua/colada-continua-01.jpg` a `09.jpg` para el visor Lightbox y la vista en imagen completa.
  - **Miniaturas optimizadas (`600 × 750 px`, proporción 4:5)** generadas en `assets/img/galeria/colada-continua/thumbs/` para carga rápida y nitidez en tarjetas sin recorte (`aspect-ratio: 4 / 5`, `width="600" height="750"`).
- **Ubicación en el portal:**
  - En **`galeria.html`**: nueva sección **«Línea de Colada Continua en Operación»** (`#galeriaColadaSection`) en grilla simétrica **3×3** ordenada según la secuencia del proceso productivo (alimentación de aluminio líquido, solidificación en rodillos, inspección superficial, avance de banda, estación de cizalla, consola de mando, vista panorámica, bobinado industrial y clase trabajadora) y acceso rápido desde el encabezado.
  - En **`index.html#galeria`**: vitrina destacada **«Línea de Colada Continua en Acción»** (4 fotografías en 4 columnas en escritorio / 2×2 en móvil) conectada al visor Lightbox para recorrer las 9 fotografías completas y saltar a `/galeria.html#galeriaColadaSection`.
- **Visor Lightbox mejorado y corrección de fotografías clasificadas:**
  - Escenario ampliado (`max-height: 74vh`) sobre fondo oscuro de alto contraste (`#0b1628`), tira de miniaturas navegable (`#lightboxThumbs`), navegación con teclado (`←`, `→`, `Esc`) y botón **Ver Imagen Completa**.
  - Se corrigió la lectura de imágenes en `initPaginaGaleria()` (`f.imagen || f.img` y `f.thumb`), restaurando la visualización de las 15 fotografías clasificadas definidas en `data/contenido.json`.
- **Paneles Dashboard Blancos y Redacción Institucional:**
  - Los encabezados y descripciones de las secciones de galería tanto en `galeria.html` (Videos, Colada Continua y Fotografías Clasificadas) como en `index.html#galeria` (Galería de Procesos y Colada Continua) fueron unificados en tarjetas dashboard blancas (`.galeria-dashboard-header`) con franja superior azul corporativo, tipografía de alto contraste (`#334155` / `#0f2942`) y proporciones equilibradas.
  - Se estilizó `.industrial-stats-grid` en una fila de 4 columnas en el panel superior de `galeria.html` y se limpió todo el texto de galería para mantener un lenguaje 100 % institucional de CVG Alcasa.
  - Versiones de caché actualizadas a `main.css?v=2026w` y `app.js?v=2026y`.

### 3.12. Mejoras en Página Principal, Mascota Fija en Empresa y Carrusel Coverflow (`index.html`, `assets/js/app.js`, `assets/css/main.css`)
- **Menú superior y navegación directa a Contacto:**
  - Se retiró la opción «Descargar Catálogo» únicamente del menú desplegable de **Productos** (conservando la descarga en el cuerpo de la página) y se renombraron las líneas del menú a **Productos de Fundición**, **Productos de Extrusión** y **Productos de Laminación**.
  - El botón **Contacto** dirige directamente a la sección **Contáctanos Directamente** (`/index.html#contacto-directo`) desde cualquier página del portal, con ajuste de desplazamiento suave (`behavior: 'instant'` por frame RAF) y eliminación de la línea blanca inferior en el header al desplazarse (`scrollY > 90`).
- **Diferenciación visual de OAC y Contáctanos Directamente:**
  - Ambas bandas fueron separadas fuera de `<footer>`: **Sistema de Atención al Ciudadano (OAC)** (`#oac-ciudadano`) con identidad de banda azul institucional y **Contáctanos Directamente** (`#contacto-directo`) sobre fondo claro con tarjeta blanca elevada y panel lateral de canales con íconos corregidos.
- **Mascota institucional siempre visible en «Más que una simple empresa»:**
  - Se reubicó `.welcome-mascot` (`assets/img/mascota-alcasa.png`) fuera de la pestaña 1 (`#tab-quienes-somos`) hacia una columna lateral fija a la derecha (`.empresa-body-layout`, `minmax(0, 1fr) 235px`, centrado vertical), manteniéndola visible al alternar entre las 4 pestañas (*¿Quiénes somos?*, *Misión y Visión*, *Nuestros Valores* y *Pilares de Gestión*) sin comprimir los textos.
  - Se ajustó `.empresa-body-layout .valores-grid` en grilla simétrica **2×2** (evitando filas desbalanceadas de 3 + 1) y apilado responsive limpio en `≤ 992px` y `≤ 600px`.
- **Carrusel Coverflow 3D de 24 fotografías y limpieza de controles:**
  - Se integró el carrusel Coverflow (`#homeGaleriaCoverflow`) con las 24 fotografías institucionales (15 clasificadas + 9 de Colada Continua, excluyendo videos), avance automático cada 7 s, pausa en hover, flechas SVG inline y barra de progreso centrada.
  - Se eliminó la opción/botón **«Ampliar fotografía»** (`#homeGaleriaExpandBtn`) del pie del carrusel junto con su manejador en `assets/js/app.js` y sus estilos `.galeria-coverflow-expand-btn` en `assets/css/main.css` (manteniendo el clic directo sobre la foto central para abrir el visor Lightbox).

### 3.13. Catálogo de 33 Productos, 9 Fotografías Recortadas y Tarjetas Compactas (`data/contenido.json`, `productos.html`, `index.html`, `assets/img/productos/*-ficha.jpg`, `assets/js/app.js`, `assets/css/main.css`)
- **Ampliación del catálogo oficial a 33 productos:** Se incorporó el producto **#33 Rollo Cinta** dentro de **Productos de Laminación** (distribución actualizada: 2 de Fundición, 24 de Extrusión y 7 de Laminación) y se actualizaron los contadores y etiquetas de filtros en `productos.html`.
- **Fotografías recortadas de producto terminado:** Se integraron 9 imágenes optimizadas (`assets/img/productos/*-ficha.jpg`, con `object-fit: contain`) recortadas para mostrar únicamente el producto terminado sin logos ni textos superpuestos.
- **Tarjetas compactas:** Altura uniforme de imagen (`158 px`), resumen en 2 líneas, chips compactos de códigos y grilla `minmax(280px, 1fr)`, conservando la tabla técnica completa dentro del modal de Ficha Técnica.

### 3.14. Galería Compacta y Visor Lightbox en Resolución Original 1:1 (`galeria.html`, `assets/js/app.js`, `assets/css/main.css`)
- **Tarjetas horizontales compactas:** Ajuste de la cuadrícula fotográfica en `galeria.html` a tarjetas horizontales compactas (4 columnas en escritorio) para mejorar densidad visual y legibilidad.
- **Visor Lightbox con zoom nativo 1:1:** Se añadieron flechas SVG inline, insignia de dimensiones (`#lightboxResBadge`), botón **Ver 1:1 (Resolución Original)** (`#lightboxNativeZoomBtn`) y enlace **Abrir Original**.
- **Versiones de caché actuales:** `main.css?v=2026z5` y `app.js?v=2026z6` en `index.html` (`main.css?v=2026z3` y `app.js?v=2026z5` en `galeria.html` y `productos.html`).
