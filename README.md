# CVG Aluminio del Caroní, S.A. (CVG Alcasa) — Portal Institucional v2

Portal web institucional de **CVG Aluminio del Caroní, S.A. (CVG Alcasa)** desarrollado en HTML5, CSS3 y JavaScript modular con backend PHP (preparado para integración directa con WordPress).

El detalle técnico completo de la arquitectura y evolución del proyecto se encuentra en `INFORME_DE_CAMBIOS.md`.

---

## Ejecución en Entorno Local

### 1. Servidor completo con PHP (Formularios, Sala de Prensa Paginada, Galería, Redacción, Contactos y CV)
```bash
php -d upload_max_filesize=12M -d post_max_size=25M -S localhost:8000
# Abrir http://localhost:8000/index.html
```

### 2. Solo vista estática (sin PHP)
```bash
python3 -m http.server 8000
# Las noticias y catálogo cargan desde los respaldos locales data/noticias.json y data/contenido.json
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
| `/contactos.html` | **Panel Administrativo de Contactos y Postulaciones (CV)** — Aplicación administrativa independiente con bandejas separadas por nivel de usuario, visor de detalle, copia para WhatsApp/Correo y descarga de CV. |
| `/minimal.html` | Vista compacta embebible de Pulso Industrial CVG Alcasa. |

---

## Niveles de Acceso y Roles Exclusivos (`storage/usuarios_config.json`)

| Nivel | Usuario | Rol / Área | Funciones Asignadas |
| :--- | :--- | :--- | :--- |
| **Nivel 1** | `admin` | Super Administrador General | Control total: Redacción de Prensa (incluyendo eliminación permanente de noticias) + Bandeja de Contactos Comerciales + Bandeja de Postulaciones de CV. |
| **Nivel 2** | `comunicacion` | Gerencia de Comunicación Estratégica | Acceso exclusivo a **Redacción de Sala de Prensa** (`/redaccion.html`): redactar, subir hasta 20 imágenes al carrusel, guardar borradores, archivar y publicar noticias. |
| **Nivel 3** | `comercializacion` | Gerencia de Comercialización | Acceso exclusivo a la **Bandeja de Contactos Comerciales y Cotizaciones** en `/contactos.html`. |
| **Nivel 4** | `talento` | Gerencia de Personal / Talento Humano | Acceso exclusivo a la **Bandeja de Postulaciones de Empleo y Síntesis Curricular (CV)** y descarga de PDFs en `/contactos.html`. |
