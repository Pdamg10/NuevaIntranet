# CVG Alcasa — Portal v2

Sitio en HTML/CSS/JS con backend PHP (destino final: WordPress). Detalle de cada cambio: `INFORME_DE_CAMBIOS.md`.

## Levantar en local (con formularios y noticias)
    php -d upload_max_filesize=12M -d post_max_size=14M -S localhost:8000
    # abrir http://localhost:8000/index.html

## Solo vista estática (sin PHP)
    python -m http.server 8000      # las APIs no funcionan; las noticias usan data/noticias.json

Las rutas son absolutas (`/assets/...`): servir esta carpeta como raíz del sitio.
Contenido editable: `data/contenido.json`. URLs de los endpoints: objeto `CFG` en `assets/js/app.js`.
