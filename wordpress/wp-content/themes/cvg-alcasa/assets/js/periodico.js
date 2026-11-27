/* ==========================================================================
   Periódico Digital CVG Alcasa — Script encolado para WordPress
   Consume el endpoint REST del plugin (ALCASA_WP.noticiasUrl) con respaldo
   automático en ALCASA_WP.noticiasFallbackUrl (data/noticias.json).
   ========================================================================== */
(function () {
    'use strict';

    var WP = window.ALCASA_WP || {};
    var API = WP.noticiasUrl || '';
    var FALLBACK = WP.noticiasFallbackUrl || '/alcasa-data/noticias.json';
    var PORTAL = WP.newsPortalUrl || 'http://alcasa.com.ve:5001';
    var MEDIA_BASE = (WP.mediaBaseUrl || '/alcasa-media').replace(/\/$/, '');
    var PERIODICO_URL = (WP.pages && WP.pages.periodico) ? WP.pages.periodico : location.pathname;

    var pagActual = 1;
    var totalPaginas = 72;
    var filtros = { q: '', fecha: '', mes: '', anio: '' };
    var detalleCache = {};
    var empujamosHistorial = false;

    function resolveMedia(u) {
        if (!u) return '';
        var s = String(u).trim();
        if (/^https?:\/\//i.test(s) || s.indexOf('data:') === 0) return s;
        if (s.indexOf('/assets/') === 0) return MEDIA_BASE + s.slice('/assets'.length);
        if (s.indexOf(MEDIA_BASE + '/') === 0) return s;
        return s.charAt(0) === '/' ? (PORTAL + s) : (PORTAL + '/' + s);
    }

    (function fechas() {
        var meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        var dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
        var hoy = new Date();
        var texto = dias[hoy.getDay()] + ', ' + hoy.getDate() + ' de ' + meses[hoy.getMonth()] + ' de ' + hoy.getFullYear();
        var fEd = ('0' + hoy.getDate()).slice(-2) + '/' + ('0' + (hoy.getMonth() + 1)).slice(-2) + '/' + hoy.getFullYear();
        var el1 = document.getElementById('fechaActual');
        var el2 = document.getElementById('fechaEdicion');
        if (el1) el1.textContent = texto;
        if (el2) el2.textContent = fEd;
    })();

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function verLista(visible) {
        document.getElementById('vistaLista').style.display = visible === false ? 'none' : 'block';
        document.getElementById('vistaNoticia').style.display = visible === false ? 'block' : 'none';
        if (visible !== false) {
            document.title = 'Periódico Digital CVG Alcasa';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function closeModales() {
        Array.prototype.forEach.call(document.querySelectorAll('.pd-modal.active'), function (m) {
            m.classList.remove('active');
        });
    }

    function flash(tipo, texto) {
        var box = document.getElementById('flashBox');
        if (!box) return;
        var icono = tipo === 'ok' ? 'fa-circle-check' : 'fa-circle-exclamation';
        box.innerHTML = '<div class="pd-alert ' + (tipo === 'ok' ? 'ok' : 'danger') + '"><i class="fas ' + icono + '"></i> <span>' + esc(texto) + '</span></div>';
        box.style.display = texto ? 'flex' : 'none';
    }

    function paramsFiltros(page) {
        var p = new URLSearchParams({ page: String(page || 1) });
        if (filtros.q) p.set('q', filtros.q);
        if (filtros.fecha) p.set('fecha', filtros.fecha);
        if (filtros.mes) p.set('mes', filtros.mes);
        if (filtros.anio) p.set('anio', filtros.anio);
        return p.toString();
    }

    function recortar(txt, max) {
        txt = String(txt || '');
        if (txt.length <= max) return txt;
        return txt.slice(0, max).replace(/\s+\S*$/, '') + '...';
    }

    function tarjeta(n) {
        var sep = PERIODICO_URL.indexOf('?') === -1 ? '?' : '&';
        var href = PERIODICO_URL + sep + 'noticia=' + encodeURIComponent(n.id);
        var imgSrc = resolveMedia(n.imagen || '/assets/img/noticias/noticia-761.jpg');
        return '' +
            '<article class="pd-card">' +
                '<img src="' + esc(imgSrc) + '" class="pd-img" alt="' + esc(n.titulo) + '" loading="lazy" decoding="async" onerror="this.onerror=null;this.outerHTML=\'<div class=&quot;pd-img-ph&quot;><i class=&quot;fas fa-image&quot;></i></div>\';">' +
                '<div class="pd-body">' +
                    '<h2 class="pd-titulo-nota"><a href="' + esc(href) + '" class="link-noticia" data-id="' + esc(n.id) + '">' + esc(n.titulo) + '</a></h2>' +
                    '<p class="pd-sub">' + esc(n.subtitulo || '') + '</p>' +
                    '<div class="pd-meta">' +
                        '<span class="pd-line"><i class="fas fa-user"></i> ' + esc(n.autor || 'Comunicación Estratégica') + '</span>' +
                        '<span class="pd-line"><i class="fas fa-calendar-day"></i> ' + esc((n.fecha_solo || '') + (n.hora_solo ? ' ' + n.hora_solo : '')) + '</span>' +
                        '<span class="pd-badge">' + esc(n.categoria || 'Institucional') + '</span>' +
                    '</div>' +
                    '<p class="pd-resumen">' + esc(recortar(n.resumen, 200)) + '</p>' +
                    '<div class="pd-acciones"><a href="' + esc(href) + '" class="pd-btn-leer link-noticia" data-id="' + esc(n.id) + '"><i class="fas fa-book-open"></i> Leer más</a></div>' +
                '</div>' +
            '</article>';
    }

    function renderPaginacion(contenedor, curr, total) {
        var c = document.getElementById(contenedor);
        if (!c) return;
        if (total <= 1) { c.style.display = 'none'; return; }
        c.style.display = 'flex';
        var html = '';
        html += curr <= 1 ? '<span class="disabled">‹</span>' : '<a href="#" class="pag-link" data-p="' + (curr - 1) + '">‹</a>';
        var startP = Math.max(1, curr - 2);
        var endP = Math.min(total, startP + 4);
        if (endP - startP < 4) startP = Math.max(1, endP - 4);
        if (startP > 1) {
            html += '<a href="#" class="pag-link" data-p="1">1</a>';
            if (startP > 2) html += '<span class="separador">…</span>';
        }
        for (var p = startP; p <= endP; p++) {
            html += (p === curr) ? '<span class="active">' + p + '</span>' : '<a href="#" class="pag-link" data-p="' + p + '">' + p + '</a>';
        }
        if (endP < total) {
            if (endP < total - 1) html += '<span class="separador">…</span>';
            html += '<a href="#" class="pag-link" data-p="' + total + '">' + total + '</a>';
        }
        html += curr >= total ? '<span class="disabled">›</span>' : '<a href="#" class="pag-link" data-p="' + (curr + 1) + '">›</a>';
        if (curr < total) html += '<a href="#" class="pag-link" data-p="' + total + '">»</a>';
        c.innerHTML = html;
    }

    function pintar(items) {
        var grid = document.getElementById('noticiasGrid');
        if (!items.length) {
            grid.innerHTML = '<div class="pd-empty"><i class="fas fa-inbox"></i> No hay noticias disponibles con los criterios seleccionados.</div>';
        } else {
            grid.innerHTML = items.map(tarjeta).join('');
        }
        renderPaginacion('paginacionTop', pagActual, totalPaginas);
        renderPaginacion('paginacionBottom', pagActual, totalPaginas);
    }

    function filtrarLocal(items) {
        return items.filter(function (n) {
            if (filtros.q) {
                var hay = ((n.titulo || '') + ' ' + (n.subtitulo || '') + ' ' + (n.resumen || '') + ' ' + (n.categoria || '')).toLowerCase();
                if (hay.indexOf(filtros.q.toLowerCase()) === -1) return false;
            }
            if (filtros.fecha) {
                var parts = filtros.fecha.split('-');
                var dmy = parts.length === 3 ? (parts[2] + '/' + parts[1] + '/' + parts[0]) : filtros.fecha;
                if (String(n.fecha_solo || '').indexOf(dmy) === -1) return false;
            }
            if (filtros.mes && String(n.fecha_solo || '').indexOf('/' + filtros.mes + '/') === -1) return false;
            if (filtros.anio && String(n.fecha_solo || '').indexOf(filtros.anio) === -1) return false;
            return true;
        });
    }

    function cargarFallback(page) {
        return fetch(FALLBACK, { cache: 'no-store' })
            .then(function (r) { if (!r.ok) throw new Error('fallback'); return r.json(); })
            .then(function (data) {
                var raw = Array.isArray(data) ? data : (data.noticias || []);
                var filtradas = filtrarLocal(raw);
                totalPaginas = 1;
                pagActual = page || 1;
                pintar(filtradas);
            })
            .catch(function () {
                pintar([]);
                flash('danger', 'No se pudo conectar con el servicio de noticias ni con el respaldo local.');
            });
    }

    function cargarPagina(page) {
        if (!API) {
            cargarFallback(page);
            return;
        }
        var sep = API.indexOf('?') === -1 ? '?' : '&';
        fetch(API + sep + paramsFiltros(page))
            .then(function (r) { if (!r.ok) throw new Error('api'); return r.json(); })
            .then(function (resp) {
                var arr = (resp && resp.noticias) || [];
                totalPaginas = (resp && resp.total_pages) ? (parseInt(resp.total_pages, 10) || 1) : 1;
                pagActual = page;
                pintar(arr);
            })
            .catch(function () {
                cargarFallback(page);
            });
    }

    function pintarDetalle(d) {
        detalleCache[String(d.id)] = d;
        var esVivo = !!d.esVivo;
        var urlDetalle = esVivo ? d.url : (location.origin + location.pathname + '?noticia=' + encodeURIComponent(d.id));
        var imgUrl = resolveMedia(d.imagen || '');
        var seccion = document.getElementById('vistaNoticia');
        seccion.innerHTML =
            '<article class="pd-detalle">' +
                '<h1 class="pd-detalle-titulo">' + esc(d.titulo) + '</h1>' +
                '<h2 class="pd-subtitulo-detalle">' + esc(d.subtitulo || '') + '</h2>' +
                '<div class="pd-meta-detalle">' +
                    '<span class="pd-line"><i class="fas fa-user"></i> ' + esc(d.autor || 'Comunicación Estratégica') + '</span>' +
                    '<span class="pd-line"><i class="fas fa-calendar-day"></i> ' + esc(d.fecha || '') + '</span>' +
                    '<span class="pd-badge">' + esc(d.categoria || 'Institucional') + '</span>' +
                '</div>' +
                '<div class="pd-compartir">' +
                    '<span>Compartir:</span>' +
                    '<a class="pd-sh fb" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(urlDetalle) + '"><i class="fab fa-facebook-f"></i> Facebook</a>' +
                    '<a class="pd-sh tw" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=' + encodeURIComponent(urlDetalle) + '&text=' + encodeURIComponent(d.titulo || '') + '"><i class="fab fa-x-twitter"></i> Twitter</a>' +
                    '<a class="pd-sh wa" target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text=' + encodeURIComponent((d.titulo || '') + ' - ' + urlDetalle) + '"><i class="fab fa-whatsapp"></i> WhatsApp</a>' +
                    '<a class="pd-sh li" target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(urlDetalle) + '"><i class="fab fa-linkedin-in"></i> LinkedIn</a>' +
                    '<a class="pd-sh email" href="mailto:?subject=' + encodeURIComponent(d.titulo || 'Comunicado CVG Alcasa') + '&body=' + encodeURIComponent('Te comparto esta noticia: ' + urlDetalle) + '"><i class="fas fa-envelope"></i> Correo</a>' +
                    '<button type="button" class="pd-sh cp" id="btnCopiarEnlace"><i class="fas fa-link"></i> Copiar</button>' +
                '</div>' +
                '<div class="pd-tabloide">' +
                    (imgUrl
                        ? '<div class="pd-imagen-principal">' +
                              '<a href="' + esc(imgUrl) + '" target="_blank" rel="noopener"><img src="' + esc(imgUrl) + '" alt="Imagen principal"></a>' +
                              (d.nombreArchivo ? '<div class="pd-nombre-archivo"><i class="fas fa-paperclip"></i> ' + esc(d.nombreArchivo) + '</div>' : '') +
                              '<div class="pd-img-btns">' +
                                  '<a class="pd-mini-btn" href="' + esc(imgUrl) + '" target="_blank" rel="noopener"><i class="fas fa-eye"></i> Ver</a>' +
                                  '<a class="pd-mini-btn" href="' + esc(imgUrl) + '" download><i class="fas fa-download"></i> Descargar</a>' +
                              '</div>' +
                          '</div>'
                        : '') +
                    '<div class="pd-texto">' + (d.cuerpo || ('<p>' + esc(d.resumen || '') + '</p>')) + '</div>' +
                '</div>' +
                '<div class="pd-acciones-detalle">' +
                    '<button type="button" class="btn-volver" id="btnVolverDetalle"><i class="fas fa-arrow-left"></i> Volver</button>' +
                    (esVivo ? '<a class="pd-mini-btn-sec" href="' + esc(urlDetalle) + '" target="_blank" rel="noopener"><i class="fas fa-globe"></i> Ver en el portal oficial</a>' : '') +
                '</div>' +
            '</article>';

        seccion.style.display = 'block';
        document.getElementById('vistaLista').style.display = 'none';
        document.title = (d.titulo || 'Comunicado') + ' — Periódico Digital CVG Alcasa';
        window.scrollTo({ top: 0, behavior: 'smooth' });

        document.getElementById('btnVolverDetalle').addEventListener('click', function () {
            if (empujamosHistorial && history.state && history.state.noticia) {
                history.back();
            } else {
                verLista(true);
                history.replaceState(null, '', location.pathname);
            }
        });

        document.getElementById('btnCopiarEnlace').addEventListener('click', function () {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(urlDetalle).then(function () {
                    flash('ok', 'Enlace copiado: ' + urlDetalle);
                }, function () {
                    flash('danger', 'No se pudo copiar el enlace.');
                });
            } else {
                flash('ok', 'Enlace de la noticia: ' + urlDetalle);
            }
        });
    }

    function abrirDetalleDesdeFallback(id) {
        return fetch(FALLBACK, { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (lista) {
                var arr = Array.isArray(lista) ? lista : (lista.noticias || []);
                var n = arr.filter(function (x) { return String(x.id) === String(id); })[0];
                if (!n) throw new Error('no encontrada');
                var imgs = (n.imagenes || []).length ? n.imagenes : [{ url: n.imagen }];
                pintarDetalle({
                    id: id,
                    esVivo: false,
                    url: PORTAL + '/noticia/' + encodeURIComponent(id),
                    titulo: n.titulo || 'Comunicado',
                    subtitulo: n.subtitulo || '',
                    categoria: n.categoria || 'Institucional',
                    autor: n.autor || 'Comunicación Estratégica',
                    fecha: (n.fecha_solo || '') + (n.hora_solo ? ' ' + n.hora_solo : ''),
                    imagen: imgs[0].url || '',
                    nombreArchivo: '',
                    cuerpo: n.cuerpo || n.resumen || ''
                });
            })
            .catch(function () {
                flash('danger', 'No se pudo cargar la noticia (' + id + ').');
            });
    }

    function abrirDetalle(id, empujar) {
        var req = API
            ? fetch(API + (API.indexOf('?') === -1 ? '?' : '&') + 'id=' + encodeURIComponent(id))
                .then(function (r) { if (!r.ok) throw new Error('api-id'); return r.json(); })
                .then(function (d) {
                    if (!d || !d.id) throw new Error('vacio');
                    pintarDetalle(d);
                })
                .catch(function () { return abrirDetalleDesdeFallback(id); })
            : abrirDetalleDesdeFallback(id);

        if (empujar !== false) {
            history.pushState({ noticia: id }, '', location.pathname + '?noticia=' + encodeURIComponent(id));
            empujamosHistorial = true;
        }
        return req;
    }

    window.addEventListener('popstate', function (e) {
        var id = e.state && e.state.noticia;
        if (id && detalleCache[String(id)]) {
            pintarDetalle(detalleCache[String(id)]);
        } else if (id) {
            abrirDetalle(id, false);
        } else {
            verLista(true);
        }
    });

    Array.prototype.forEach.call(document.querySelectorAll('[data-open-modal]'), function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var m = document.getElementById('modal-' + this.getAttribute('data-open-modal'));
            if (m) m.classList.add('active');
        });
    });
    Array.prototype.forEach.call(document.querySelectorAll('[data-close-modal]'), function (btn) {
        btn.addEventListener('click', function () {
            var m = document.getElementById('modal-' + this.getAttribute('data-close-modal'));
            if (m) m.classList.remove('active');
        });
    });
    Array.prototype.forEach.call(document.querySelectorAll('.pd-modal'), function (ov) {
        ov.addEventListener('click', function (e) {
            if (e.target === this) this.classList.remove('active');
        });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModales();
    });

    var btnArriba = document.getElementById('pdIrArriba');
    var btnAbajo = document.getElementById('pdIrAbajo');
    if (btnArriba) btnArriba.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
    if (btnAbajo) btnAbajo.addEventListener('click', function () { window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }); });

    function aplicarFiltros() {
        filtros.q = (document.getElementById('txtBuscar').value || '').trim();
        filtros.fecha = document.getElementById('fecha').value || '';
        filtros.mes = document.getElementById('mes').value || '';
        filtros.anio = document.getElementById('anio').value || '';
        cargarPagina(1);
    }

    function limpiarFiltros() {
        filtros = { q: '', fecha: '', mes: '', anio: '' };
        ['txtBuscar', 'fecha', 'mes', 'anio'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.value = '';
        });
        cargarPagina(1);
    }

    document.getElementById('btnBuscar').addEventListener('click', aplicarFiltros);
    document.getElementById('btnLimpiar').addEventListener('click', limpiarFiltros);
    ['paginacionTop', 'paginacionBottom'].forEach(function (con) {
        document.getElementById(con).addEventListener('click', function (e) {
            var a = e.target.closest('.pag-link');
            if (a) { e.preventDefault(); cargarPagina(parseInt(a.getAttribute('data-p'), 10) || 1); }
        });
    });
    document.getElementById('txtBuscar').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); aplicarFiltros(); }
    });
    document.getElementById('noticiasGrid').addEventListener('click', function (e) {
        var a = e.target.closest('.link-noticia');
        if (a) { e.preventDefault(); abrirDetalle(a.getAttribute('data-id')); }
    });

    var navInicioEl = document.getElementById('navInicio');
    if (navInicioEl) {
        navInicioEl.addEventListener('click', function (e) {
            if (document.getElementById('vistaNoticia').style.display !== 'none') {
                e.preventDefault();
                verLista(true);
                history.pushState(null, '', location.pathname);
            }
        });
    }

    var qs = new URLSearchParams(location.search);
    var urlDetalle = qs.get('noticia');
    cargarPagina(1);
    if (urlDetalle) {
        history.replaceState({ noticia: urlDetalle }, '', location.href);
        abrirDetalle(urlDetalle, false);
    }
})();
