# Informe Detallado de Cambios — Portal CVG Alcasa v2 (Rama `copia`)

## 1. Resumen Ejecutivo
Se consolidó el **Portal Institucional v2 de CVG Aluminio del Caroní, S.A. (CVG Alcasa)** en la rama `copia`, unificando la identidad visual corporativa con una arquitectura limpia y modular en HTML/CSS/JS y backend PHP (`/api/`).

---

## 2. Estructura Actual del Proyecto
```text
/
├── index.html                  # Portal principal institucional
├── productos.html              # Catálogo Oficial 2025 completo (32 productos en 3 líneas)
├── prensa.html                 # Sala de Prensa "Pulso Industrial CVG Alcasa" (Top 10 + Archivo)
├── redaccion.html              # Panel editorial de 3 zonas (hasta 20 fotos por noticia + historial)
├── contactos.html              # Bandejas administrativas de Contactos Comerciales y Postulaciones CV
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
│   ├── noticias.php            # Feed unificado de noticias (locales + servidor :5001) con soporte de galería
│   └── prensa_admin.php        # Autenticación por niveles, CRUD de noticias, carrusel (20 fotos) e historial
├── assets/
│   ├── css/
│   │   └── main.css            # Sistema de diseño corporativo unificado
│   ├── js/
│   │   └── app.js              # Motor interactivo del portal
│   ├── img/                    # Logos, favicon.png, fotografías de plantas, noticias y 32 productos
│   └── media/                  # Catálogos PDF oficiales (2023 y 2025)
├── data/
│   ├── contenido.json          # Datos institucionales, 32 productos con fichas técnicas, FAQ y videos
│   └── noticias.json           # Almacén y respaldo local de noticias con galería de imágenes
└── storage/
    ├── .htaccess               # Bloqueo de acceso web directo a archivos privados
    └── usuarios_config.json    # Configuración de los 4 niveles de usuarios y permisos exclusivos
```

---

## 3. Detalle de Cambios Recientes Implementados

### 3.1. Identidad Visual y Favicon Institucional
- Se generó el ícono oficial del sitio (`assets/img/favicon.png` y `/favicon.ico`) a partir del **logo completo de la "A" metálica de CVG Alcasa con fondo transparente** (`assets/img/logo-alcasa.png`), ajustando sus márgenes para máxima nitidez en pestañas de navegador.
- Se enlazó `<link rel="icon">` y `<link rel="apple-touch-icon">` en todas las páginas del proyecto (`index.html`, `productos.html`, `prensa.html`, `contactos.html`, `redaccion.html` y `minimal.html`).

### 3.2. Miniaturas de Noticias con Carrusel Automático Limpio
- En `assets/js/app.js` (`renderCardNoticia`) y en la vista previa de `redaccion.html`:
  - Se eliminaron de la miniatura de cada noticia las flechas laterales de navegación, el contador numérico (`1/N`), el ícono de galería y las insignias superpuestas.
  - Ahora la miniatura muestra únicamente las fotografías de la noticia pasando automáticamente en forma de carrusel limpio (cada 6 segundos, con pausa al pasar el cursor).

### 3.3. Separación Estricta de los 4 Niveles de Usuario y Permisos (`api/prensa_admin.php`, `api/admin_datos.php`, `redaccion.html`, `contactos.html`)
Se corrigió el control de acceso para que cada cuenta acceda únicamente a la información y herramientas de su gerencia:
1. **Nivel 1 — Super Administrador (`admin`):**
   - Permisos: `["prensa", "prensa_eliminar", "contactos", "cv"]`.
   - Acceso total a la Sala de Redacción (único rol autorizado para eliminar noticias permanentemente) y acceso simultáneo a las bandejas de *Contactos Comerciales* y *Postulaciones de CV*.
2. **Nivel 2 — Gerencia de Comunicación Estratégica (`comunicacion`):**
   - Permisos: `["prensa"]`.
   - Acceso exclusivo a `redaccion.html` para crear, editar, subir hasta 20 imágenes por noticia, guardar borradores, archivar y publicar. Bloqueado en `contactos.html` y `api/admin_datos.php` (`HTTP 403`).
3. **Nivel 3 — Gerencia de Comercialización (`comercializacion`):**
   - Permisos: `["contactos"]`.
   - Acceso exclusivo en `contactos.html` a la **Bandeja de Contactos Comerciales y Cotizaciones**. La pestaña de Postulaciones de CV y el editor de prensa permanecen ocultos y bloqueados.
4. **Nivel 4 — Gerencia de Personal / Talento Humano (`talento`):**
   - Permisos: `["cv"]`.
   - Acceso exclusivo en `contactos.html` a la **Bandeja de Postulaciones de Empleo y Síntesis Curricular (CV)** y a la descarga de currículums en PDF/DOC/DOCX. La pestaña de Contactos Comerciales y el editor de prensa permanecen ocultos y bloqueados.

### 3.4. Sección de Oportunidades Laborales Simplificada y Conectada (`index.html`, `api/cv.php`, `contactos.html`)
- En la sección `#oportunidades` de `index.html`, se eliminó la tarjeta lateral informativa dejando **únicamente el bloque centrado "Envía tu Síntesis Curricular (CV)"** con su formulario `#formPostulacionCV`.
- El formulario está conectado directamente con `/api/cv.php` y formatea cada postulación con la estructura compatible con `/contactos.html` (`Fecha`, `Nombre (C.I: ...)`, `Correo`, `Teléfono`, `Empresa/Nivel: Postulación CV — ...`, `Interés/Área: Oportunidad Laboral: ...`, `Resumen/Comentario` y `Archivo` descargable), permitiendo visualizar la ficha completa, copiar en formato WhatsApp/Correo y descargar el CV adjunto desde la bandeja de Talento Humano (`contactos.html`).
