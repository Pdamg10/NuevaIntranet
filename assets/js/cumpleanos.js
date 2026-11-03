/* =========================================================
   Cumpleañeros — lista por día/mes + tarjeta emergente descargable
   Datos:  /data/cumpleaneros.json  (se genera con tools/generar_cumpleaneros.py)
   Fotos:  /assets/img/cumpleaneros/<ficha>.jpg  (si falta, se usa una silueta)
   ========================================================= */
(function () {
    'use strict';

    var DATA_URL = '/data/cumpleaneros.json';
    var FOTOS_URL = '/assets/img/cumpleaneros/';
    var H2C_URL = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
    var JSPDF_URL = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
    var SALIDA_ANCHO = 810; // px de la imagen/PDF descargados (proporción 9:16 de la plantilla)
    var MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    var $ = function (id) { return document.getElementById(id); };
    var lista = [];
    var actual = null;
    var ultimoFoco = null;
    var hoy = new Date();
    var scripts = {};

    function el(tag, className, text) {
        var n = document.createElement(tag);
        if (className) n.className = className;
        if (text !== undefined) n.textContent = text; // textContent: los datos nunca se interpretan como HTML
        return n;
    }
    function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
    function pad(n) { return String(n).padStart(2, '0'); }

    /* ---------- Fotos ---------- */
    var MASCULINOS_EN_A = ['JOSHUA', 'LUCA', 'NOA', 'ELIA'];
    var FEMENINOS = ['BEATRIZ', 'CARMEN', 'ISABEL', 'RAQUEL', 'SHEYLA', 'GLADYS', 'MERCEDES', 'ESTHER'];
    function silueta(nombre) {
        var primero = (nombre || '').split(' ')[0].toUpperCase();
        var fem = (/A$/.test(primero) && MASCULINOS_EN_A.indexOf(primero) < 0) || FEMENINOS.indexOf(primero) >= 0;
        return FOTOS_URL + (fem ? 'silueta_femenina.png' : 'silueta_masculina.png');
    }
    function ponerFoto(img, p) {
        img.onerror = function () { this.onerror = null; this.src = silueta(p.nombre); };
        img.src = p.foto || (FOTOS_URL + p.ficha + '.jpg');
    }

    /* ---------- Filtros y lista ---------- */
    function poblarFiltros() {
        var d = $('cumple-dia'), m = $('cumple-mes');
        d.appendChild(new Option('Todos los días', '0'));
        for (var i = 1; i <= 31; i++) d.appendChild(new Option(String(i), String(i)));
        MESES.forEach(function (n, i) { m.appendChild(new Option(cap(n), String(i + 1))); });
        d.value = String(hoy.getDate());
        m.value = String(hoy.getMonth() + 1);
        d.addEventListener('change', buscar);
        m.addEventListener('change', buscar);
        $('cumple-consultar').addEventListener('click', buscar);
    }

    function tarjeta(p, mostrarFecha) {
        var b = el('button', 'cumple-card');
        b.type = 'button';
        b.setAttribute('aria-label', 'Ver tarjeta de cumpleaños de ' + p.nombre);

        var fw = el('div', 'cumple-card-foto');
        var img = el('img');
        img.alt = ''; img.loading = 'lazy';
        ponerFoto(img, p);
        fw.appendChild(img);

        var info = el('div', 'cumple-card-info');
        info.appendChild(el('p', 'cumple-card-ficha', 'FICHA: ' + p.ficha));
        info.appendChild(el('h3', 'cumple-card-nombre', p.nombre));
        var dep = el('p', 'cumple-card-dato'); dep.appendChild(el('b', null, 'DEPARTAMENTO')); dep.appendChild(document.createTextNode(p.departamento || '—'));
        var ger = el('p', 'cumple-card-dato'); ger.appendChild(el('b', null, 'GERENCIA'));     ger.appendChild(document.createTextNode(p.gerencia || '—'));
        info.appendChild(dep); info.appendChild(ger);
        if (mostrarFecha) info.appendChild(el('p', 'cumple-card-fecha', p.dia + ' de ' + MESES[p.mes - 1]));

        b.appendChild(fw); b.appendChild(info);
        b.addEventListener('click', function () { abrir(p, b); });
        return b;
    }

    function buscar() {
        var dia = parseInt($('cumple-dia').value, 10);
        var mes = parseInt($('cumple-mes').value, 10);
        var grid = $('cumple-grid');
        grid.textContent = '';

        var res = lista.filter(function (p) { return p.mes === mes && (dia === 0 || p.dia === dia); })
                       .sort(function (a, b) { return a.dia - b.dia || a.nombre.localeCompare(b.nombre, 'es'); });

        $('cumple-mensaje').hidden = !res.length;
        if (!res.length) {
            grid.appendChild(el('p', 'cumple-vacio', 'No se encontraron cumpleañeros para esta fecha.'));
            return;
        }
        var frag = document.createDocumentFragment();
        res.forEach(function (p) { frag.appendChild(tarjeta(p, dia === 0)); });
        grid.appendChild(frag);
        celebrar();
    }

    /* Confeti de celebración al mostrar cumpleañeros (como en la página original) */
    function celebrar() {
        if (typeof window.confetti !== 'function') return;
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        window.confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 }, zIndex: 99999 });
    }

    /* ---------- Ventana emergente ---------- */
    function ajustarTarjeta() {
        var w = Math.min(405, (window.innerHeight - 140) * 9 / 16, window.innerWidth * 0.92);
        w = Math.max(220, Math.floor(w));
        var t = $('cumple-tarjeta');
        t.style.width = w + 'px';
        t.style.height = Math.round(w * 16 / 9) + 'px';
        t.style.setProperty('--u', (w / 100) + 'px');
    }

    function abrir(p, disparador) {
        actual = p;
        ultimoFoco = disparador || null;
        ponerFoto($('ct-foto'), p);
        $('ct-nombre').textContent = p.nombre;
        $('ct-ficha').textContent = 'FICHA: ' + p.ficha;
        $('ct-depto').textContent = p.departamento || '—';
        $('ct-gcia').textContent = p.gerencia || '—';
        $('ct-fecha').textContent = pad(p.dia) + '/' + pad(p.mes) + '/' + hoy.getFullYear();
        ajustarTarjeta();
        var m = $('cumple-modal');
        m.classList.add('activo');
        m.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        $('cumple-cerrar').focus();
    }

    function cerrar() {
        var m = $('cumple-modal');
        m.classList.remove('activo');
        m.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (ultimoFoco) ultimoFoco.focus();
    }

    /* ---------- Descarga (imagen / PDF) ---------- */
    function cargarScript(url) {
        if (!scripts[url]) {
            scripts[url] = new Promise(function (ok, fallo) {
                var s = document.createElement('script');
                s.src = url; s.onload = ok;
                s.onerror = function () { delete scripts[url]; fallo(new Error('No se pudo cargar ' + url)); };
                document.head.appendChild(s);
            });
        }
        return scripts[url];
    }

    function esperarImagenes(raiz) {
        return Promise.all(Array.prototype.map.call(raiz.querySelectorAll('img'), function (img) {
            if (img.complete && img.naturalWidth > 0) return null;
            return new Promise(function (r) { img.addEventListener('load', r, { once: true }); img.addEventListener('error', r, { once: true }); });
        }));
    }

    function capturar() {
        var t = $('cumple-tarjeta');
        return cargarScript(H2C_URL).then(function () { return esperarImagenes(t); }).then(function () {
            return window.html2canvas(t, {
                scale: SALIDA_ANCHO / t.offsetWidth, useCORS: true, backgroundColor: null,
                logging: false, scrollX: 0, scrollY: 0,
                onclone: function (doc) { // esquinas cuadradas y sin sombra en el archivo descargado
                    var c = doc.getElementById('cumple-tarjeta');
                    c.style.borderRadius = '0'; c.style.boxShadow = 'none';
                }
            });
        });
    }

    function nombreArchivo() {
        return 'Feliz-Cumpleanos-' + actual.nombre.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-zA-Z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    }

    function conEstado(btn, tarea) {
        var html = btn.innerHTML;
        $('cumple-btn-img').disabled = $('cumple-btn-pdf').disabled = true;
        btn.textContent = 'Generando…';
        return tarea().catch(function (e) {
            console.error(e);
            alert('No se pudo generar el archivo. Verifica la conexión (se necesitan librerías externas) e inténtalo de nuevo.');
        }).then(function () {
            btn.innerHTML = html;
            $('cumple-btn-img').disabled = $('cumple-btn-pdf').disabled = false;
        });
    }

    function descargarImagen() {
        if (!actual) return;
        conEstado($('cumple-btn-img'), function () {
            return capturar().then(function (canvas) {
                return new Promise(function (ok) {
                    canvas.toBlob(function (blob) {
                        var a = document.createElement('a');
                        a.href = URL.createObjectURL(blob); a.download = nombreArchivo() + '.png';
                        document.body.appendChild(a); a.click(); a.remove();
                        setTimeout(function () { URL.revokeObjectURL(a.href); }, 2000);
                        ok();
                    }, 'image/png');
                });
            });
        });
    }

    function descargarPDF() {
        if (!actual) return;
        conEstado($('cumple-btn-pdf'), function () {
            return Promise.all([capturar(), cargarScript(JSPDF_URL)]).then(function (r) {
                var c = r[0], jsPDF = window.jspdf.jsPDF;
                var pdf = new jsPDF({ orientation: 'portrait', unit: 'px', format: [c.width, c.height], compress: true });
                pdf.addImage(c.toDataURL('image/jpeg', 0.95), 'JPEG', 0, 0, c.width, c.height);
                pdf.save(nombreArchivo() + '.pdf');
            });
        });
    }

    /* ---------- Inicio ---------- */
    function init() {
        if (!$('cumple-grid')) return;
        poblarFiltros();
        $('cumple-cerrar').addEventListener('click', cerrar);
        $('cumple-modal').addEventListener('click', function (e) { if (e.target === this) cerrar(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && $('cumple-modal').classList.contains('activo')) cerrar(); });
        window.addEventListener('resize', function () { if ($('cumple-modal').classList.contains('activo')) ajustarTarjeta(); });
        $('cumple-btn-img').addEventListener('click', descargarImagen);
        $('cumple-btn-pdf').addEventListener('click', descargarPDF);

        fetch(DATA_URL, { cache: 'no-cache' })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (d) {
                if (d.titulo) $('cumple-titulo').textContent = d.titulo;
                if (d.mensaje) $('cumple-texto').textContent = '"' + d.mensaje + '"';
                lista = Array.isArray(d.cumpleaneros) ? d.cumpleaneros : [];
                buscar();
            })
            .catch(function () {
                $('cumple-grid').appendChild(el('p', 'cumple-vacio',
                    'No se pudo cargar ' + DATA_URL + '. Abre el sitio con un servidor local (python -m http.server 8000).'));
            });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
