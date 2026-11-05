# Informe Detallado de Cambios — Portal CVG Alcasa v2 (Rama `copia`)

## 1. Resumen Ejecutivo
Se consolidó el **Portal Institucional v2 de CVG Aluminio del Caroní, S.A. (CVG Alcasa)** en la rama `copia`, integrando una experiencia interactiva moderna (ventanas emergentes flotantes para fichas técnicas, noticias, videos y fotografías; barra de navegación inteligente que se oculta al deslizar; galería multimedia dedicada con previsualizaciones en GIF; y conexión paginada con el servidor de noticias `:5001`), el módulo **«Nuestros Cumpleañeros»** con las fotos reales del personal (silueta por género cuando no hay retrato y tarjeta de felicitación ampliada), y paneles administrativos independientes por nivel de usuario: la Redacción de Sala de Prensa, el **Panel de Contactos, exclusivo de los mensajes del formulario de contacto**, y el **Panel de Postulaciones (CV), exclusivo de la bandeja de Síntesis Curricular** para `talento` (con visibilidad también para `admin`).

---

## 2. Estructura Actual del Proyecto
```text
/
├── index.html                  # Portal principal institucional
├── productos.html              # Catálogo Oficial 2025 completo (32 productos con Ficha Técnica flotante)
├── galeria.html                # Galería Multimedia dedicada (Videos completos + Fotografías clasificadas)
├── prensa.html                 # Sala de Prensa "Pulso Industrial CVG Alcasa" (Paginación :5001 + Modal de vista previa)
├── redaccion.html              # Aplicación administrativa independiente de Redacción (login «Acceso Restringido», 3 zonas, hasta 20 fotos)
├── contactos.html              # Panel unificado de Contactos Comerciales y Postulaciones (CV) (login «Acceso Restringido», pestañas por rol, tarjetas Ver·WA·Copiar·Eliminar/CV)
├── sitio/Empresa/CA/index.html # Página «Nuestros Cumpleañeros» (filtro día/mes, tarjetas con silueta por género y ventana emergente con plantilla)
├── tools/generar_cumpleaneros.py # Genera data/cumpleaneros.json y las fotos desde DATA.csv + BMP
├── minimal.html                  # Periódico Digital CVG Alcasa (estructura y funciones del portal :5001 con el diseño copia de la intranet, login integrado)
├── favicon.ico                 # Favicon multirresolución con el logo metálico de CVG Alcasa
├── INFORME_DE_CAMBIOS.md       # Documentación técnica detallada de cambios
├── README.md                   # Guía de inicio rápido y matriz de niveles de acceso
├── web.config                  # Reglas de seguridad para IIS
├── .gitignore                  # Exclusión de datos personales generados (storage/*.csv, storage/cv/)
├── api/                        # Backend modular en PHP
│   ├── _lib.php                # Utilidades compartidas (sanitización CSV, JSON/FormData, correo)
│   ├── admin_datos.php         # API protegida por rol: contactos y postulaciones (solo lectura) + descarga de CV
│   ├── contacto.php            # Recepción de formulario de contacto comercial (storage/contactos.csv)
│   ├── cv.php                  # Recepción de Síntesis Curricular (PDF/DOC/DOCX) y registro estructurado
│   ├── noticias.php            # Feed paginado de noticias (locales + servidor http://alcasa.com.ve:5001/?page=N)
│   └── prensa_admin.php        # Autenticación por niveles, CRUD de noticias, carrusel (20 fotos) e historial
├── assets/
│   ├── css/
│   │   ├── cumpleanos.css      # Estilos de las fichas de cumpleañeros (reutiliza variables de main.css)
│   │   └── main.css            # Sistema de diseño corporativo unificado y modales flotantes de alto contraste
│   ├── js/
│   │   ├── cumpleanos.js       # Renderiza las fichas desde data/cumpleaneros.json
│   │   └── app.js              # Motor interactivo del portal (catálogo, galería, noticias, modales, header scroll)
│   ├── img/
│   │   ├── galeria/            # Miniaturas animadas (.gif) para los videos industriales
│   │   └── ...                 # Logos institucionales, favicon.png, fotografías de plantas, noticias y 32 productos
│   └── media/
│       ├── videos/             # Videos institucionales completos en MP4 (Laminación, Fundición, Extrusión, Gestión Social)
│       └── ...                 # Catálogos PDF oficiales (2023 y 2025)
├── data/
│   ├── contactos-semilla.csv   # Semilla versionada de la bandeja de contactos (8 registros copiados del servidor vivo alcasa.com.ve)
│   ├── postulaciones-semilla.csv # Semilla versionada de la bandeja de postulaciones de CV (encabezados)
│   ├── cumpleaneros.json       # Cumpleañeros (3.543 trabajadores, generado desde DATA.csv)
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
  - Se crearon y aplicaron clases con alto contraste cromático: `.btn-primary-corp` (azul institucional con texto blanco), `.btn-modal-secondary` (azul marino `#054a91` con borde celeste `#60a5fa` y texto blanco), `.btn-modal-close` (gris pizarra `#334155` con texto blanco y hover rojo `#dc2626`), `.btn-cta-banner-white` (fondo blanco puro con letras azul marino `#054a91`) y `.btn-prensa-quickview-sm` (azul oscuro `#054a91` con texto blanco).

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
- **Nivel 1 (`admin`):** Control total: Redacción (incluyendo eliminación permanente) + Panel de Contactos (`/contactos.html`, exclusivo de contactos desde 3.12) + Panel de Postulaciones (CV) (`/cv.html`, nuevo desde 3.14).
- **Nivel 2 (`comunicacion`):** Acceso exclusivo a Redacción de Sala de Prensa (`/redaccion.html`).
- **Nivel 3 (`comercializacion`):** Acceso exclusivo al Panel de Contactos en `/contactos.html` (módulo exclusivo de contactos desde 3.12).
- **Nivel 4 (`talento`):** Acceso exclusivo al **Panel de Postulaciones (CV)** (`/cv.html`, nuevo desde 3.14): gestiona la bandeja de Síntesis Curricular conectada al formulario único **Envía tu Síntesis Curricular (CV)** de `index.html` (recepción y archivo en `storage/postulaciones.csv` + PDF en `storage/cv/` vía `api/cv.php`).

### 3.7. Actualización de la Paleta Azul Institucional (`assets/css/main.css`, `index.html`, `productos.html`, `prensa.html`, `galeria.html`, `contactos.html`, `redaccion.html`, `minimal.html`)
- **Nuevo azul base:** Se reemplazó la paleta azul anterior (`#003366` / `#002244` / `#0056a3`) por el azul de la cabecera y pie de `http://alcasa.com.ve:8070/`:
  - Azul principal: `#367EC6` (`--alcasa-blue` y `--alcoa-blue`).
  - Azul oscuro: `#236EB8` (`--alcasa-blue-dark`).
  - Azul claro de apoyo: `#5B9BD5` (`--alcasa-light-blue`).
  - Azul profundo complementario: `#054A91` (menús, modales y botones secundarios).
  - Azul institucional profundo: `#002285` (barra de idiomas y cabecera del menú móvil).
- **Cabecera y pie:** Los degradados de `#site-header`, la cabecera al hacer scroll y el pie de página se recalcularon sobre los nuevos tonos; la cabecera conserva el acento naranja institucional.
- **Componentes secundarios:** Se actualizaron sombras, bordes, insignias, tooltips, paneles de noticias, modales y botones que tenían azules fijos, manteniendo el texto blanco y el contraste.
- **Caché:** Se actualizó la versión de `main.css` a `?v=2026s` en todas las páginas para forzar la recarga de estilos.

### 3.8. Página «Cumpleañeros» — `sitio/Empresa/CA/index.html` (nuevos archivos y modificaciones)
- **`sitio/Empresa/CA/index.html` (reemplaza `Index.html`):** La versión anterior (`Index.html`, con estilos propios, `DATA.csv`, `/components/header.html` y `/js/translator.js`, inexistentes en la nueva estructura) se renombró y se reescribió con la estructura actual: `#site-header` y `#site-footer` (los renderiza `assets/js/app.js`), `main.css` compartido y `content-wrapper`. Incluye filtro día/mes con botón «Consultar», cuadro de felicitación, tarjetas pequeñas (foto, ficha, nombre, departamento, gerencia) y una ventana emergente con la tarjeta sobre la plantilla. La versión previa sigue en el historial de git (commit `a0ff640`).
- **`data/cumpleaneros.json` (nuevo):** 3.543 trabajadores tomados de `DATA.csv` (rama `main`): `ficha`, `nombre`, `departamento`, `gerencia`, `dia`, `mes`. Además `titulo` y `mensaje` del cuadro de felicitación. No incluye el año de nacimiento. Campo opcional `foto` para forzar otra imagen.
- **`assets/img/cumpleaneros/` (nuevo):** 3.355 fotos `<ficha>.jpg` (recorte cuadrado, ~21 MB en total) convertidas desde los BMP de `FotosAdabas/` (200 px), más `silueta_masculina.png` y `silueta_femenina.png` para los 188 trabajadores sin foto. El respaldo `FotoBmp2/` (100 px) aporta solo 1 foto útil (`24790.jpg`); el resto de sus BMP son ampliaciones con una «X» dibujada y se descartan (ver 3.10).
- **Encabezado y confeti (conservados de la página original en `main`):** cuadro «🎂 ¡Feliz Cumpleaños te desea CVG Alcasa! 🎂» con título en degradado, mensaje entre comillas y la indicación «Haz clic en cualquier tarjeta…»; al mostrar cumpleañeros se lanza la animación de confeti (`canvas-confetti` 1.5.1 desde jsDelivr; se omite si el usuario prefiere menos movimiento).
- **`assets/js/cumpleanos.js` (nuevo):** Carga el JSON, filtra por día/mes (hoy por defecto; «Todos los días» lista el mes completo), pinta las tarjetas con `textContent` (los datos nunca se interpretan como HTML), abre la ventana emergente y genera la descarga en PNG (810×1440) o PDF con html2canvas y jsPDF (cargados bajo demanda desde cdnjs).
- **`assets/css/cumpleanos.css` (nuevo):** Estilos de la sección. La tarjeta emergente usa `assets/img/plantilla-cumpleanos.png` como fondo y se dimensiona con una variable `--u` (1 % del ancho), de modo que la pantalla, la imagen y el PDF salen iguales.
- **`assets/img/plantilla-cumpleanos.png` (nuevo):** `PLANTILLA.png` (2560×4551, 6,4 MB) optimizada a 810×1440 px (~0,9 MB), proporción 9:16.
- **`tools/generar_cumpleaneros.py` (nuevo):** Regenera el JSON y las fotos desde `DATA.csv` y los BMP (`python tools/generar_cumpleaneros.py --csv ... --fotos ...`). Conserva `titulo` y `mensaje` del JSON existente.
- **`assets/js/app.js` e `index.html` (modificados):** Se agregó el enlace «Cumpleañeros» (`/sitio/Empresa/CA/index.html`) al menú desplegable **Extranet**, después de «Sistema OAC» (header renderizado por `app.js` y header estático de `index.html`).
- **Datos personales:** el JSON y las fotos contienen datos de trabajadores. Si el repositorio es público, conviene hacerlo privado o publicar estos datos solo en el servidor interno.
- **Migración a PHP/WordPress:** El JSON puede sustituirse por un endpoint/`WP_Query` con el mismo formato; el HTML ya separa datos y presentación.

### 3.9. Acento acero y ventanas flotantes blancas (`assets/css/main.css`, `index.html`, `productos.html`, `prensa.html`, `galeria.html`, `minimal.html`, `contactos.html`, `redaccion.html`, `assets/js/app.js`)
- **Nueva variable de acento (acero, inspirada en el logo metálico de ALCASA):** `--alcasa-accent: #4F6B80`, `--alcasa-accent-hover: #3F5668` y, para superficies oscuras, `--alcasa-accent-light: #C7D1DA` con hover `#E3E9EF`. Se eliminó el naranja `#FF6B1A` en todas sus formas (variables, rgba, gradientes y estilos inline de HTML/`app.js`).
- **Reparto de contextos:** el azul institucional `#367EC6 / #236EB8` sigue siendo el color principal; el acero queda como acento secundario en tarjetas, columnas del pie, indicadores, botones de filtro activos, enlaces «ver más» y anillos de foco. Sobre fondos oscuros, los botones primarios usan degradados claro-acero (`#E3E9EF → #C7D1DA`) con texto azul `#054A91`, y los acentos decorativos usan el acero claro.
- **Ventanas flotantes (fichas técnicas, noticias, video y lightbox) en tema claro:** fondo blanco del diálogo, bloque del cuerpo blanco, cabecera con degradado azul (`#054A91 → #236EB8 → #367EC6`) y acentos en acero claro; backdrop azul (`rgba(2, 26, 58, 0.78)`). Se restylearon los botones primarios (degradado azul), secundarios (blanco con borde `#B9D4EC`) y de cierre (hover en azul claro); texto y tablas con la paleta clara (`#334155`, cabecera `#EAF3FB`).
- **Caché:** se subió `main.css` a `?v=2026t` y `app.js` a `?v=2026s` en todas las páginas.

### 3.10. Siluetas en Cumpleañeros: se eliminaron las fichas con «X» dibujada (`assets/img/cumpleaneros/`, `tools/generar_cumpleaneros.py`)
- **Diagnóstico:** 67 fotos generadas desde el respaldo `FotoBmp2/` eran en realidad ampliaciones idénticas de un BMP «vacío» con una «X» dibujada (mismo hash MD5, `d297f361…`), por lo que sus tarjetas mostraban esa «X» en lugar del retrato. La página original del sitio (servida desde `FotosAdabas/`) las considera sin foto y muestra la silueta.
- **Solución:** se borraron esas 67 imágenes (`assets/img/cumpleaneros/25***.jpg` y otros de 26***), de modo que `cumpleanos.js` cae por `onerror` a la silueta del género estimada (`silueta_masculina.png` / `silueta_femenina.png`), igual que en la página de referencia. Se conservó `24790.jpg` (MARLIS DEL VALLE CAMPOS ACOSTA), la única foto real extraíble del respaldo. Balances: 3.355 fotos reales y 188 trabajadores con silueta sobre un total de 3.543.
- **`tools/generar_cumpleaneros.py`:** ahora descarta automáticamente cualquier BMP «vacío» (menos del 15 % de píxeles oscuros al recortar al cuadrado), evitando reintroducir fichas con la «X». Las futuras regeneraciones con `--fotos … --fotos FotoBmp2` salen ya limpias.
- **Ventana emergente más grande:** la tarjeta de la plantilla pasó de 405 px a 480 px de ancho (al máximo que permita el viewport, proporción 9:16 intacta; `--u` y el JS de ajuste escalan todo automáticamente). La imagen/PDF descargados siguen saliendo a 810 px. Caché: `cumpleanos.css/js` a `?v=4`.

### 3.11. Menú «Extranet» alineado con la rama `main` (`assets/js/app.js`, `index.html`)
- **Corrección:** el submenú «Extranet» del portal `copia` tenía otro orden, el rótulo corto «Cumpleañeros» y una entrada extra «Sistema OAC» (`190.202.49.114:5050`) que no existe en `main`; además faltaba «Panel de Contactos». Se dejó idéntico al de `main:components/header.html`: 1) Consulta clave ARI · 2) Nuestros Cumpleañeros (`/sitio/Empresa/CA/index.html`) · 3) Acceso a la mensajería institucional (`alcasa.com.ve:5002`) · 4) Panel de Contactos (`/contactos.html`, la versión rediseñada de la rama `copia`).
- **Aplicado en ambos puntos de renderizado:** el menú estático de `index.html` y el inyectado por `assets/js/app.js` (galería, prensa, productos, etc.).
- **Caché:** `app.js` a `?v=2026u` en las cinco páginas que lo cargan.

### 3.12. Panel de Contactos con la apariencia de `contactos.html` viva y alcance exclusivo de contactos (`contactos.html`)
- **Módulo solo de contactos (verificado contra `main`):** como en la rama `main`, `contactos.html` atiende única y exclusivamente los mensajes del formulario de contacto; se retiraron la bandeja de Postulaciones de CV, sus pestañas y el acceso a Redacción (cada uno vive en su propio módulo).
- **Login (pantalla completa):** se recreó el «Acceso Restringido» de la página viva — fondo azul degradado a pantalla completa, tarjeta blanca centrada con candado circular, etiquetas USUARIO/CONTRASEÑA en versalitas, campos con icono (persona/llave), visor de contraseña («ojo»), botón «Ingresar» degradado y aviso «Acceso solo para personal autorizado». El inicio de sesión por PHP (`api/prensa_admin.php`) quedó restringido a las cuentas con permiso de Contactos (`admin` y `comercializacion`); cualquier otra cuenta válida recibe «Esta cuenta no tiene permiso para el módulo de Contactos.» y las cuentas rápidas del desplegable «Cuentas autorizadas» se filtran a esas mismas cuentas.
- **Panel (sección privada):** barra superior azul de ancho completo con «Administrador de Contactos», cápsulas de conteo («X contactos», «X mostrados»), acceso al Portal y botón blanco-cápsula «Salir»; debajo **barra de filtros** (búsqueda + desplegable «Todos los intereses» con la lista fija copiada de `main`: Cotización, Fundición, Laminación, Extrusión, Comercialización, Catálogo de productos, Otro + «Recargar») y **cuadrícula de tarjetas** (3 columnas): nombre en mayúsculas, insignia del interés (acento acero en lugar de naranja), fecha/correo/empresa con iconos de acero, cita del comentario y fila de acciones **Ver · WA · Copiar · Eliminar**.
- **«Eliminar»:** marca la tarjeta localmente con superposición «Marcado para eliminar» y barra roja con conteo y «Desmarcar todos»; la marca se recuerda en `localStorage` (misma mecánica que la página viva) y no borra del servidor (la API de datos es de solo lectura).
- **Conservado:** modal de detalle con formatos de copia (WhatsApp/correo/todo) para responder al remitente.

### 3.13. Documentación alineada con el estado del proyecto (`README.md`, `INFORME_DE_CAMBIOS.md`)
- **`README.md`:** descripción de `/contactos.html` como panel exclusivo de contactos (login restringido, tarjetas y marca local); se añadió la página `/sitio/Empresa/CA/index.html` de «Nuestros Cumpleañeros» a la tabla de páginas; la matriz de niveles refleja la distribución vigente (Nivel 4 con permiso `cv` reservado mientras no exista bandeja dedicada en `copia`); nota de datos personales.
- **`INFORME_DE_CAMBIOS.md`:** árbol de proyecto actualizado (`contactos.html`, `admin_datos.php`, cumpleañeros); sección 3.6 ajustada a los módulos vigentes; secciones 3.10/3.11/3.12 quedaron en orden cronológico; documentado el estado de la bandeja de CV (pendiente de módulo propio, sin perder su recepción y archivo).

### 3.14. Panel de Postulaciones (CV) — `/cv.html` nuevo módulo exclusivo de `talento` + `admin` (`cv.html`, `api/admin_datos.php`, `redaccion.html`, `assets/js/app.js`, `index.html`, `contactos.html`, `data/*-semilla.csv`)
- **Nueva página `/cv.html` (nuevo archivo):** módulo administrativo dedicado a las postulaciones del formulario *Envía tu Síntesis Curricular (CV)* de `index.html`, con el mismo lenguaje visual del panel de contactos (ver 3.12). Ya no queda «pendiente un módulo propio» para la bandeja de CV (ver 3.6 y 3.13): la recepción y el archivo siguen en `api/cv.php` (`storage/postulaciones.csv` + PDF en `storage/cv/`) y la gestión ahora tiene su propia página.
- **Login restringido (pantalla completa):** reproducción del «Acceso Restringido» del panel de contactos (fondo azul degradado, tarjeta blanca, campos con icono y visor de contraseña), autenticado contra `api/prensa_admin.php`. Solo ingresan las cuentas con permiso `cv`: `admin` (Nivel 1) y `talento` (Nivel 4). El editor de prensa (`comunicacion`) es redirigido a `/redaccion.html`; el resto recibe «Esta cuenta no tiene permiso para el módulo de Postulaciones (CV).»
- **Panel (sección privada):** barra superior azul «Administrador de Postulaciones (CV)» con cápsulas «X postulaciones / X mostrados», **barra de filtros** (búsqueda + desplegable «Todas las áreas» con la lista fija de las 7 áreas del formulario de CV de `index.html` + «Recargar») y **cuadrícula de tarjetas** con nombre en mayúsculas + C.I., insignia acero del área, fecha/correo/teléfono/nivel académico con iconos de acero, cita del resumen profesional y acciones **Ver · WA · Copiar · CV**. El botón «CV» descarga el PDF adjunto vía `admin_datos.php?tipo=descargar_cv` (solo lectura, sin escritura en el servidor) y queda deshabilitado si la postulación no tiene archivo.
- **Modal de detalle** con los formatos de copia WhatsApp/Correo/Todo y el enlace «Descargar CV Adjunto», igual que en contactos.
- **Semillas versionadas (`data/*-semilla.csv` + fallback en `api/admin_datos.php` e inicialización en `api/_lib.php`):** función `leerCsvDeFuente($archivo, $semilla)` que sirve `storage/*.csv` si existen y, si no (instalación limpia, sin datos personales generados), la semilla versionada de `data/`. Además, `csv_append()` en `api/_lib.php` pre-copia automáticamente la semilla de `data/` cuando `storage/*.csv` se crea por primera vez al recibir un nuevo envío, evitando perder los registros históricos iniciales. `data/contactos-semilla.csv` trae los **8 registros copiados literalmente del servidor vivo `http://alcasa.com.ve/contactos.csv`** (incluye el mensaje de ANABELL JIMENEZ del 2026-11-02 que falta en la raíz de `main`); `data/postulaciones-semilla.csv` nace solo con los encabezados.
- **Menú «Extranet» con «Panel de CV»:** entrada añadida después de «Panel de Contactos» en ambos puntos de renderizado (`assets/js/app.js` y el header estático de `index.html`). **Divergencia intencional respecto de `main`:** la rama `main` no tiene este panel; la fuente aquí es `copia`. Caché: `app.js` a `?v=2026v` en las cinco páginas que lo cargan (`index.html`, `galeria.html`, `prensa.html`, `productos.html` y `sitio/Empresa/CA/index.html`).
- **Redacción con redirección por nivel y botones separados (`redaccion.html`):** el Login de la Redacción ya no manda a toda cuenta sin prensa al panel de contactos: `comercializacion` (Nivel 3) → `/contactos.html`; `talento` (Nivel 4) → `/cv.html`; las demás reciben el mensaje de «sin permisos». En la barra superior del editor se separaron los botones **Contactos** (`/contactos.html`) y **Postulaciones (CV)** (`/cv.html`) según los permisos de la sesión.
- **Enlaces cruzados entre paneles:** `contactos.html` muestra «Postulaciones (CV)» en su barra superior (si la sesión tiene permiso `cv`) y `cv.html` muestra «Contactos» (si la sesión tiene permiso de contactos), de modo que `admin` puede saltar entre los tres módulos (`Redacción`, `Contactos` y `CV`); cada cuenta exclusiva solo ve su propio panel.
- **`README.md` actualizado:** filas de páginas `/cv.html` y `/contactos.html`, matriz de niveles (admin con los tres módulos; talento con su panel exclusivo) y nota de datos personales que menciona las semillas versionadas.

### 3.15. Periódico Digital con login y vista de noticia individual (`minimal.html`, `api/noticias.php`)
- **Distribución fiel al portal `:5001` con el diseño de la intranet (`minimal.html`):** recrea la estructura del portal vivo `http://alcasa.com.ve:5001/` con `main.css` + tipografía *Inter* + Font Awesome:
  1. **Franja superior (`.pd-top`):** degradado azul con `📢 CVG Alcasa - Periódico Digital | 📅 Fecha | 📍 Puerto Ordaz, Venezuela`.
  2. **Cabecera editorial blanca (`.pd-cabecera`):** logo institucional de CVG Alcasa, título «Periódico Digital / Información institucional» y recuadro «Edición N° 001 / DD/MM/AAAA».
  3. **Barra de navegación (`.pd-navbar`):** enlaces **Inicio**, **Publicar Nueva Noticia** (visible con sesión de prensa), **Portal** (`/index.html`) e **Iniciar/Cerrar Sesión**.
  4. **Vista de listado (`#vistaLista`):** tarjeta de filtros (buscador con lupa, fecha, desplegables Mes/Año y botones degradados **Buscar** / **Limpiar**), paginación en píldoras (`‹ 1 2 3 4 5 … 72 › »` arriba y abajo), sección «Últimas Noticias» y cuadrícula de tarjetas con borde superior azul Alcasa, miniatura de 180 px, título, subtítulo, metadatos, insignia de categoría acero, resumen y botón **Leer más**.
  5. **Vista de noticia individual tipo tabloide (`#vistaNoticia` con URL `?noticia=ID` y soporte `pushState`/`popstate`):** al hacer clic en el título o en **Leer más**, abre la noticia dentro del mismo Periódico Digital con la distribución de `/noticia/N` de `:5001`: título, subtítulo, metadatos, barra **Compartir** (Facebook, Twitter/X, WhatsApp, LinkedIn, Correo y Copiar enlace), tarjeta lateral de imagen principal con nombre de archivo adjunto y botones **Ver** / **Descargar** (con URLs relativas `/uploads/...` normalizadas automáticamente hacia `http://alcasa.com.ve:5001`), cuerpo completo del comunicado y botones **Volver**, **Ver en el portal oficial** y **Editar (Redacción)**.
- **Soporte de detalle individual en `api/noticias.php?id=N`:** el backend actúa como proxy enriquecido y caché del detalle `/noticia/N` de `:5001` (normalizando rutas `/uploads/...` y sirviendo también noticias creadas localmente en Redacción o desde el respaldo `data/noticias.json`), con fallback directo en el cliente si se ejecuta sin PHP.
- **Login «Acceso Restringido» integrado (shell igual a Contactos/CV):** la opción «Iniciar Sesión» conmuta a la vista de candado con fondo degradado y tarjeta blanca «Acceso Restringido — Periódico Digital · Gestión Editorial», autenticando contra `api/prensa_admin.php`: con permiso de prensa muestra aviso de bienvenida, habilita **Publicar Nueva Noticia / Cerrar Sesión** y añade la franja **✏️ Editar (Redacción)** en cada tarjeta y en el detalle individual.
- **Autónoma pero coherente:** funciona como página independiente y como respaldo dentro del `<iframe>` de `index.html` y `prensa.html`.

### 3.16. Unificación de CV en el Panel de Contactos y Separación de Login/Redacción en el Periódico Digital (`contactos.html`, `minimal.html`, `redaccion.html`, `index.html`, `assets/js/app.js`)
- **Eliminación de `/cv.html` e integración en `/contactos.html`:** se eliminó por completo la página redundante `/cv.html` y su entrada en el menú Extranet (`index.html` y `assets/js/app.js`). Ahora el **Panel de Contactos (`/contactos.html`)** maneja tanto **Contactos Comerciales** como **Postulaciones de CV / Empleo** en un único panel:
  - Si ingresa `admin` (Nivel 1), dispone de pestañas conmutables **Contactos Comerciales** y **Postulaciones de CV / Empleo** con sus respectivos filtros fijos (intereses comerciales vs. áreas de postulación), tarjetas (**Ver · WA · Copiar · Eliminar** en contactos y **Ver · WA · Copiar · CV** con descarga del PDF adjunto en postulaciones) y enlace a **Redacción**.
  - Si ingresa `comercializacion` (Nivel 3), visualiza directamente la bandeja de Contactos Comerciales.
  - Si ingresa `talento` (Nivel 4), visualiza directamente la bandeja de Postulaciones de CV.
- **Separación estricta entre el portal de noticias (`minimal.html`) y el módulo de edición (`redaccion.html`):**
  - Al iniciar sesión desde el login «Acceso Restringido» de `minimal.html`, el usuario ya no se queda dentro del portal de noticias ni se mezclan botones administrativos en la vista pública: es redirigido de inmediato a la página independiente `/redaccion.html` (o a `/contactos.html` si su cuenta es de comercialización/talento).
- **Nuevo login «Acceso Restringido» en `redaccion.html` al cerrar sesión o entrar sin autenticar:**
  - Se reemplazó el antiguo formulario de login de `redaccion.html` por el nuevo diseño «Acceso Restringido» a pantalla completa (fondo degradado azul, tarjeta blanca `.lg-card`, visor de contraseña con icono de ojo y desplegable de cuentas autorizadas / retorno a `/minimal.html`, `/prensa.html` e `/index.html`). Al cerrar sesión desde `redaccion.html`, muestra este nuevo login en lugar del viejo.


