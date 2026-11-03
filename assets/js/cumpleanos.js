/* =========================================================
   Cumpleañeros — renderiza las fichas desde /data/cumpleaneros.json
   Para cambiar datos basta con editar el JSON (no se toca el HTML).
   ========================================================= */
(function () {
    'use strict';

    var DATA_URL = '/data/cumpleaneros.json';
    var PLANTILLA_URL = '/assets/img/plantilla-cumpleanos.png';
    var MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    var $ = function (id) { return document.getElementById(id); };
    var lista = [];
    var hoy = new Date();

    function el(tag, className, text) {
        var n = document.createElement(tag);
        if (className) n.className = className;
        if (text !== undefined) n.textContent = text; // textContent: el JSON nunca se interpreta como HTML
        return n;
    }

    function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

    function poblarFiltros() {
        var selDia = $('cumple-dia');
        var selMes = $('cumple-mes');
        selDia.appendChild(new Option('Todos los días', '0'));
        for (var d = 1; d <= 31; d++) selDia.appendChild(new Option(String(d), String(d)));
        MESES.forEach(function (m, i) { selMes.appendChild(new Option(cap(m), String(i + 1))); });
        selMes.value = String(hoy.getMonth() + 1);
        selDia.addEventListener('change', render);
        selMes.addEventListener('change', render);
    }

    function ficha(p) {
        var esHoy = p.dia === hoy.getDate() && p.mes === hoy.getMonth() + 1;
        var card = el('article', 'cumple-card' + (esHoy ? ' es-hoy' : ''));
        var box = el('div', 'cumple-contenido');
        var img = el('img', 'cumple-foto');
        img.src = p.foto || '/assets/img/placeholder.svg';
        img.alt = 'Foto de ' + (p.nombre || 'trabajador');
        img.loading = 'lazy';
        img.onerror = function () { this.onerror = null; this.src = '/assets/img/placeholder.svg'; };
        card.appendChild(img);

        box.appendChild(el('h3', 'cumple-nombre', p.nombre || 'Nombre Apellido'));
        box.appendChild(el('p', 'cumple-cargo', p.cargo || 'Cargo'));
        box.appendChild(el('p', 'cumple-depto', p.departamento || 'Departamento'));

        var f = el('p', 'cumple-fecha');
        f.appendChild(el('i', 'fas fa-cake-candles'));
        f.appendChild(document.createTextNode(p.dia + ' de ' + MESES[p.mes - 1]));
        box.appendChild(f);
        if (esHoy) box.appendChild(el('span', 'cumple-badge-hoy', '¡Hoy!'));

        card.appendChild(box);
        return card;
    }

    function render() {
        var dia = parseInt($('cumple-dia').value, 10);
        var mes = parseInt($('cumple-mes').value, 10);
        var grid = $('cumple-grid');
        grid.textContent = '';

        var filtrados = lista
            .filter(function (p) { return p.mes === mes && (dia === 0 || p.dia === dia); })
            .sort(function (a, b) { return a.dia - b.dia; });

        $('cumple-resumen').textContent = filtrados.length + (filtrados.length === 1 ? ' cumpleañero' : ' cumpleañeros') +
            ' en ' + MESES[mes - 1] + (dia ? ' (día ' + dia + ')' : '');

        if (!filtrados.length) {
            grid.appendChild(el('p', 'cumple-vacio', 'No hay cumpleañeros para la fecha seleccionada.'));
            return;
        }
        filtrados.forEach(function (p) { grid.appendChild(ficha(p)); });
    }

    /* Ajusta la proporción de las fichas a la de PLANTILLA para no deformarla */
    function ajustarProporcion() {
        var im = new Image();
        im.onload = function () {
            if (im.naturalWidth && im.naturalHeight) {
                $('cumple-grid').style.setProperty('--cumple-ratio', im.naturalWidth + ' / ' + im.naturalHeight);
            }
        };
        im.src = PLANTILLA_URL;
    }

    function init() {
        if (!$('cumple-grid')) return;
        poblarFiltros();
        ajustarProporcion();
        fetch(DATA_URL, { cache: 'no-cache' })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (d) {
                if (d.titulo)  $('cumple-titulo').textContent = d.titulo;
                if (d.mensaje) $('cumple-texto').textContent = d.mensaje;
                lista = Array.isArray(d.cumpleaneros) ? d.cumpleaneros : [];
                render();
            })
            .catch(function () {
                $('cumple-grid').appendChild(el('p', 'cumple-vacio',
                    'No se pudo cargar ' + DATA_URL + '. Abre el sitio con un servidor local (python -m http.server 8000).'));
            });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
