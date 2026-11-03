/* ==========================================================================
   CVG ALUMINIO DEL CARONÍ, S.A. (CVG ALCASA) — MOTOR PRINCIPAL DEL PORTAL (v2)
   Integra:
   - Configuración desacoplada (CFG: contenido.json y endpoints PHP en /api/)
   - Header Corporativo 3 Zonas (Auto-hide al deslizar) + Selector Multi-idioma + WhatsApp Flotante
   - Animaciones BlurText y Fold-Reveal (IntersectionObserver)
   - Slider Plano del Hero con soporte táctil y autoplay
   - Sistema de Pestañas en Sección Empresa
   - Catálogo Interactivo de Productos 2025 + Filtros + Ficha Técnica Inline + Cotizador
   - Acordeón de Preguntas Frecuentes (FAQ)
   - Pulso Industrial CVG Alcasa (Top 10 Noticias con Miniatura, Título, Descripción, Fecha y Hora)
   - Oportunidades Laborales y Carga Real de CV (/api/cv.php)
   - Galería de Procesos con Visor de Video Inline
   - Formulario de Contacto (/api/contacto.php)
   ========================================================================== */
(function () {
    'use strict';

    var CFG = Object.assign({
        contenido: '/data/contenido.json',
        noticias:  '/api/noticias.php',
        respaldo:  '/data/noticias.json',
        contacto:  '/api/contacto.php',
        cv:        '/api/cv.php'
    }, window.ALCASA_CFG || {});

    var PH = '/assets/img/placeholder.svg';
    var C = {};

    function $(s, r) {
        return (r || document).querySelector(s);
    }
    function $$(s, r) {
        return Array.prototype.slice.call((r || document).querySelectorAll(s));
    }
    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }
    function decodificarUnicode(txt) {
        if (!txt) return '';
        return String(txt).replace(/\\u([\dA-Fa-f]{4})/g, function (_, hex) {
            return String.fromCharCode(parseInt(hex, 16));
        });
    }
    function normalizarCVG(txt) {
        if (!txt) return '';
        var s = String(txt);
        s = s.replace(/\b(?<!CVG\s)Aluminios?\s+del\s+Caron[íi],?\s*S\.?A\.?/gi, 'CVG Aluminio del Caroní, S.A.');
        s = s.replace(/\b(?<!CVG\s)(?<!-)ALCASA\b/g, 'CVG ALCASA');
        s = s.replace(/\b(?<!CVG\s)(?<!-)Alcasa\b/g, 'CVG Alcasa');
        s = s.replace(/\bCVG\s+CVG\s+/gi, 'CVG ');
        return s;
    }
    function getJSON(u) {
        return fetch(u, { cache: 'no-store' }).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.text();
        }).then(function (txt) {
            if (!txt || txt.trim().indexOf('<?php') === 0) throw new Error('PHP no interpretado');
            return JSON.parse(txt);
        });
    }

    /* ======================================================================
       1. ANIMACIÓN BLURTEXT (FADE-UP LIMPIO)
       ====================================================================== */
    var BlurText = (function () {
        function animateElement(el, options) {
            if (!el) return;
            var opts = Object.assign({
                threshold: 0.1,
                rootMargin: '0px 0px -20px 0px',
                immediate: false
            }, options || {});

            if (opts.immediate) {
                el.classList.remove('simple-text-visible');
                el.classList.add('simple-text-reveal');
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        el.classList.add('simple-text-visible');
                        el.setAttribute('data-blur-text-completed', 'true');
                    });
                });
                return;
            }

            el.classList.add('simple-text-reveal');
            var animated = false;
            function trigger() {
                if (animated) return;
                animated = true;
                requestAnimationFrame(function () {
                    el.classList.add('simple-text-visible');
                    el.setAttribute('data-blur-text-completed', 'true');
                });
            }

            if ('IntersectionObserver' in window) {
                var obs = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            trigger();
                            obs.unobserve(entry.target);
                        }
                    });
                }, { threshold: opts.threshold, rootMargin: opts.rootMargin });
                obs.observe(el);
            } else {
                setTimeout(trigger, 50);
            }
        }

        function initAll() {
            $$('[data-blur-text], .blur-text').forEach(function (el) {
                if (el.getAttribute('data-blur-text-initialized')) return;
                el.setAttribute('data-blur-text-initialized', 'true');
                animateElement(el, { immediate: el.hasAttribute('data-immediate') });
            });
        }

        return { animate: animateElement, init: initAll };
    })();
    window.BlurText = BlurText;

    /* ======================================================================
       2. TRADUCTOR GLOBAL MULTI-IDIOMA (GOOGLE TRANSLATE)
       ====================================================================== */
    (function initTranslator() {
        var DEFAULT_LANG = 'es';
        var SUPPORTED = ['es', 'en', 'pt', 'fr', 'de', 'it', 'ar', 'zh-CN', 'ja'];
        var COOKIE_NAME = 'googtrans';

        function setLangCookie(lang) {
            var value = '/' + DEFAULT_LANG + '/' + lang;
            var expires = new Date(Date.now() + 365 * 864e5).toUTCString();
            document.cookie = COOKIE_NAME + '=' + value + ';path=/;expires=' + expires;
            document.cookie = COOKIE_NAME + '=' + value + ';path=/;domain=' + location.hostname + ';expires=' + expires;
        }

        function clearLangCookie() {
            var past = 'Thu, 01 Jan 1970 00:00:00 GMT';
            document.cookie = COOKIE_NAME + '=;path=/;expires=' + past;
            document.cookie = COOKIE_NAME + '=;path=/;domain=' + location.hostname + ';expires=' + past;
        }

        function aplicarIdiomaEnVivo(lang) {
            if (lang === DEFAULT_LANG) {
                clearLangCookie();
                window.location.reload();
                return;
            }
            var combo = $('.goog-te-combo');
            if (!combo) {
                var intentos = (aplicarIdiomaEnVivo.intentos || 0) + 1;
                aplicarIdiomaEnVivo.intentos = intentos;
                if (intentos > 25) {
                    aplicarIdiomaEnVivo.intentos = 0;
                    window.location.reload();
                    return;
                }
                setTimeout(function () { aplicarIdiomaEnVivo(lang); }, 200);
                return;
            }
            aplicarIdiomaEnVivo.intentos = 0;
            if (combo.value === lang) return;
            combo.value = lang;
            combo.dispatchEvent(new Event('change'));
        }

        window.alcosaSetLanguage = function (lang) {
            if (SUPPORTED.indexOf(lang) === -1) return;
            if (lang === DEFAULT_LANG) clearLangCookie();
            else setLangCookie(lang);
            aplicarIdiomaEnVivo(lang);
        };

        window.googleTranslateElementInit = function () {
            if (!window.google || !window.google.translate) return;
            if (!document.getElementById('google_translate_element')) {
                var div = document.createElement('div');
                div.id = 'google_translate_element';
                document.body.appendChild(div);
            }
            new window.google.translate.TranslateElement({
                pageLanguage: DEFAULT_LANG,
                includedLanguages: SUPPORTED.join(','),
                autoDisplay: false
            }, 'google_translate_element');
        };

        if (!document.querySelector('script[src*="translate_a/element.js"]')) {
            var s = document.createElement('script');
            s.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
            s.async = true;
            document.head.appendChild(s);
        }
    })();

    /* ======================================================================
       3. DESPLAZAMIENTO SUAVE DETERMINÍSTICO
       ====================================================================== */
    var activeScrollRaf = null;
    function cancelSmoothScroll() {
        if (activeScrollRaf) {
            cancelAnimationFrame(activeScrollRaf);
            activeScrollRaf = null;
        }
    }
    window.addEventListener('wheel', cancelSmoothScroll, { passive: true });
    window.addEventListener('touchstart', cancelSmoothScroll, { passive: true });

    function smoothScrollTo(targetY, duration) {
        duration = duration || 500;
        cancelSmoothScroll();
        var startY = window.pageYOffset || document.documentElement.scrollTop || 0;
        var destY = Math.max(0, targetY);
        var diff = destY - startY;
        if (Math.abs(diff) < 2) {
            window.scrollTo(0, destY);
            return;
        }
        var startTime = performance.now();
        function step(now) {
            var elapsed = now - startTime;
            var progress = Math.min(elapsed / duration, 1);
            var ease = progress < 0.5
                ? 4 * progress * progress * progress
                : 1 - Math.pow(-2 * progress + 2, 3) / 2;
            window.scrollTo(0, Math.round(startY + diff * ease));
            if (progress < 1) {
                activeScrollRaf = requestAnimationFrame(step);
            } else {
                window.scrollTo(0, destY);
                activeScrollRaf = null;
            }
        }
        activeScrollRaf = requestAnimationFrame(step);
    }

    function bindSmoothScrollLinks(root) {
        $$('a[href*="#"], a.header-inicio-link', root || document).forEach(function (anchor) {
            if (anchor.dataset.scrollBound === '1') return;
            anchor.dataset.scrollBound = '1';

            anchor.addEventListener('click', function (e) {
                var rawHref = this.getAttribute('href') || '';
                if (this.classList.contains('nav-link-dropdown') && window.innerWidth <= 992) {
                    return;
                }

                var tabTarget = this.getAttribute('data-tab-target');
                if (tabTarget) {
                    var targetBtn = $('.empresa-tab-btn[data-tab="' + tabTarget + '"]');
                    if (targetBtn) targetBtn.click();
                }

                var catTarget = this.getAttribute('data-cat') || this.getAttribute('data-category');
                if (catTarget) {
                    var filterBtn = $('.product-filter-btn[data-filter="' + catTarget + '"]');
                    if (filterBtn) filterBtn.click();
                }

                var isHomePage = !!document.getElementById('heroSlider');
                if (isHomePage && (this.classList.contains('header-inicio-link') || rawHref === '/' || rawHref.endsWith('#inicio') || rawHref === '#top')) {
                    e.preventDefault();
                    $$('.alcoa-header .nav-list > li').forEach(function (li) { li.classList.remove('active'); });
                    var firstLi = $('.alcoa-header .nav-list > li:first-child');
                    if (firstLi) firstLi.classList.add('active');
                    smoothScrollTo(0, 500);
                    return;
                }

                var hashIndex = rawHref.indexOf('#');
                if (hashIndex === -1) return;
                var targetId = rawHref.slice(hashIndex);
                if (!targetId || targetId === '#') return;

                var targetElement = $(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.classList.add('revealed');
                    var parentReveal = targetElement.closest('.fold-reveal');
                    if (parentReveal) parentReveal.classList.add('revealed');

                    var elementPosition = targetElement.getBoundingClientRect().top;
                    var rawDest = elementPosition + (window.pageYOffset || document.documentElement.scrollTop || 0);
                    var headerOffset = 105;
                    smoothScrollTo(rawDest - headerOffset, 520);
                }
            });
        });
    }

    /* ======================================================================
       4. HEADER CORPORATIVO UNIFICADO Y FOOTER INSTITUCIONAL
       ====================================================================== */
    function isStandaloneBackofficePage() {
        var p = (window.location.pathname || '').toLowerCase();
        return p.indexOf('redaccion') !== -1 || p.indexOf('contactos') !== -1;
    }

    function renderHeader() {
        if (isStandaloneBackofficePage()) return;
        var h = $('#site-header') || $('#header-container');
        if (!h) return;
        var isPrensa = window.location.pathname.indexOf('prensa') !== -1;
        var isProductos = window.location.pathname.indexOf('productos') !== -1;
        var isGaleria = window.location.pathname.indexOf('galeria') !== -1;

        h.innerHTML = '' +
            '<div class="alcoa-langbar" translate="no">' +
                '<div class="container langbar-inner">' +
                    '<span class="langbar-institucional-tag">' +
                        '<i class="fas fa-industry"></i> CVG Aluminio del Caroní, S.A. (CVG Alcasa) — Portal Institucional e Intranet' +
                    '</span>' +
                    '<div class="langbar-controls">' +
                        '<label for="alcoa-lang-select" class="alcoa-langbar-label">' +
                            '<i class="fas fa-globe"></i> Idioma:' +
                        '</label>' +
                        '<select id="alcoa-lang-select" class="alcoa-langbar-select" aria-label="Seleccionar idioma">' +
                            '<option value="es">Español</option>' +
                            '<option value="en">English</option>' +
                            '<option value="pt">Português</option>' +
                            '<option value="fr">Français</option>' +
                            '<option value="de">Deutsch</option>' +
                            '<option value="it">Italiano</option>' +
                            '<option value="ar">العربية</option>' +
                            '<option value="zh-CN">中文</option>' +
                            '<option value="ja">日本語</option>' +
                        '</select>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<header class="alcoa-header" id="mainHeader">' +
                '<div class="container">' +
                    '<div class="header-top">' +
                        '<button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Abrir menú de navegación" aria-expanded="false">' +
                            '<i class="fas fa-bars"></i>' +
                        '</button>' +
                        '<div class="header-logo-left">' +
                            '<a href="/index.html#inicio" class="header-inicio-link" title="CVG Alcasa - Inicio">' +
                                '<img src="/assets/img/logo-alcasa.png?v=2026m" width="190" height="106" loading="eager" decoding="sync" alt="Logo CVG Alcasa" class="logo-alcasa">' +
                            '</a>' +
                        '</div>' +
                        '<div class="menu-overlay" id="menuOverlay"></div>' +
                        '<nav class="main-navigation" id="mainNav" aria-label="Navegación principal">' +
                            '<div class="mobile-drawer-header">' +
                                '<div style="display:flex;align-items:center;gap:10px;">' +
                                    '<img src="/assets/img/logo-alcasa.png?v=2026m" alt="CVG Alcasa" style="height:42px;width:auto;object-fit:contain;">' +
                                    '<span class="mobile-drawer-title">CVG Alcasa</span>' +
                                '</div>' +
                                '<button class="mobile-close-btn" id="mobileCloseBtn" aria-label="Cerrar menú">' +
                                    '<i class="fas fa-times"></i>' +
                                '</button>' +
                            '</div>' +
                            '<ul class="jelly-menu nav-list">' +
                                '<li class="' + (!isPrensa && !isProductos && !isGaleria ? 'active' : '') + '">' +
                                    '<a href="/index.html#inicio" class="header-inicio-link">Inicio</a>' +
                                '</li>' +
                                '<li class="nav-item-has-dropdown">' +
                                    '<a href="/index.html#empresa" class="nav-link-dropdown">' +
                                        '<span>Empresa</span>' +
                                        '<i class="fas fa-chevron-down dropdown-icon"></i>' +
                                    '</a>' +
                                    '<ul class="dropdown-menu">' +
                                        '<li><a href="/index.html#empresa" data-tab-target="tab-quienes-somos">¿Quiénes somos?</a></li>' +
                                        '<li><a href="/index.html#empresa" data-tab-target="tab-mision-vision">Misión y Visión</a></li>' +
                                        '<li><a href="/index.html#empresa" data-tab-target="tab-valores">Nuestros Valores</a></li>' +
                                        '<li><a href="/index.html#empresa" data-tab-target="tab-pilares">Pilares de Gestión</a></li>' +
                                        '<li class="dropdown-divider"></li>' +
                                        '<li><a href="http://190.202.49.114:5050/" target="_blank" rel="noopener noreferrer">Oficina de Atención al Ciudadano</a></li>' +
                                    '</ul>' +
                                '</li>' +
                                '<li class="nav-item-has-dropdown ' + (isProductos ? 'active' : '') + '">' +
                                    '<a href="/productos.html" class="nav-link-dropdown">' +
                                        '<span>Productos</span>' +
                                        '<i class="fas fa-chevron-down dropdown-icon"></i>' +
                                    '</a>' +
                                    '<ul class="dropdown-menu">' +
                                        '<li><a href="/index.html#productos">Destacados en Inicio (2)</a></li>' +
                                        '<li><a href="/productos.html">Catálogo Completo 2025 (32)</a></li>' +
                                        '<li><a href="/productos.html?linea=Fundición">Cilindros / Fundición (2)</a></li>' +
                                        '<li><a href="/productos.html?linea=Extrusión">Perfilería / Extrusión (24)</a></li>' +
                                        '<li><a href="/productos.html?linea=Laminación">Laminados / Laminación (6)</a></li>' +
                                        '<li><a href="/index.html#faq">Preguntas Frecuentes (FAQ)</a></li>' +
                                        '<li class="dropdown-divider"></li>' +
                                        '<li><a href="/assets/media/CATALOGO_CVG_ALCASA_2025.pdf" target="_blank" rel="noopener noreferrer"><i class="fas fa-file-pdf" style="margin-right:6px;color:var(--alcasa-accent-light);"></i>Descargar Catálogo CVG Alcasa 2025 (PDF)</a></li>' +
                                    '</ul>' +
                                '</li>' +
                                '<li>' +
                                    '<a href="/index.html#oportunidades">Oportunidades</a>' +
                                '</li>' +
                                '<li class="nav-item-has-dropdown ' + (isPrensa ? 'active' : '') + '">' +
                                    '<a href="/prensa.html" class="nav-link-dropdown">' +
                                        '<span>Actualidad</span>' +
                                        '<i class="fas fa-chevron-down dropdown-icon"></i>' +
                                    '</a>' +
                                    '<ul class="dropdown-menu">' +
                                        '<li><a href="/index.html#prensa">Pulso Industrial (Top 10 Noticias)</a></li>' +
                                        '<li><a href="/prensa.html">Portal Pulso Industrial CVG Alcasa</a></li>' +
                                        '<li><a href="/minimal.html">Periódico Digital CVG Alcasa (Vista Minimal)</a></li>' +
                                    '</ul>' +
                                '</li>' +
                                '<li class="nav-item-has-dropdown ' + (isGaleria ? 'active' : '') + '">' +
                                    '<a href="/galeria.html" class="nav-link-dropdown">' +
                                        '<span>Galería</span>' +
                                        '<i class="fas fa-chevron-down dropdown-icon"></i>' +
                                    '</a>' +
                                    '<ul class="dropdown-menu">' +
                                        '<li><a href="/galeria.html">Galería Completa (Videos y Fotos)</a></li>' +
                                        '<li><a href="/galeria.html?tipo=videos">Videos de Procesos Completos (4)</a></li>' +
                                        '<li><a href="/galeria.html?cat=Planta%20de%20Laminaci%C3%B3n">Planta de Laminación</a></li>' +
                                        '<li><a href="/galeria.html?cat=Planta%20de%20Fundici%C3%B3n">Planta de Fundición</a></li>' +
                                        '<li><a href="/galeria.html?cat=Planta%20de%20Extrusi%C3%B3n">Planta de Extrusión</a></li>' +
                                        '<li><a href="/galeria.html?cat=Compromiso%20Social">Compromiso Social e Instalaciones</a></li>' +
                                        '<li class="dropdown-divider"></li>' +
                                        '<li><a href="/index.html#galeria">Vista Rápida en Inicio</a></li>' +
                                    '</ul>' +
                                '</li>' +
                                '<li class="nav-item-has-dropdown">' +
                                    '<a href="#" class="nav-link-dropdown">' +
                                        '<span>Extranet</span>' +
                                        '<i class="fas fa-chevron-down dropdown-icon"></i>' +
                                    '</a>' +
                                    '<ul class="dropdown-menu dropdown-menu-right">' +
                                        '<li><a href="http://alcasa.com.ve/sitio/buscador_embedido.html" target="_blank" rel="noopener noreferrer">Consulta clave ARI</a></li>' +
                                        '<li><a href="http://alcasa.com.ve:5002" target="_blank" rel="noopener noreferrer">Acceso a la mensajería institucional</a></li>' +
                                        '<li><a href="http://190.202.49.114:5050/" target="_blank" rel="noopener noreferrer">Sistema OAC</a></li>' +
                                        '<li><a href="/sitio/Empresa/CA/index.html">Cumpleañeros</a></li>' +
                                    '</ul>' +
                                '</li>' +
                                '<li class="nav-item-cta">' +
                                    '<a href="#contacto">Contacto</a>' +
                                '</li>' +
                            '</ul>' +
                        '</nav>' +
                        '<div class="header-logo-right">' +
                            '<a href="/index.html#inicio" class="header-inicio-link" title="Corporación Venezolana de Guayana - CVG Alcasa">' +
                                '<img src="/assets/img/logo-cvg.png?v=2026m" width="180" height="76" loading="eager" decoding="sync" alt="Logo CVG y CVG Alcasa" class="logo-segundo logo-cvg-blanco">' +
                            '</a>' +
                            '<div class="logo-divider"></div>' +
                            '<a href="/index.html#inicio" class="header-inicio-link" title="Gobierno Bolivariano de Venezuela">' +
                                '<img src="/assets/img/logo-institucional.png?v=2026m" width="175" height="52" loading="eager" decoding="sync" alt="Logo Institucional" class="logo-institucional">' +
                            '</a>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="header-scroll-fade" id="headerScrollFade" aria-hidden="true"></div>' +
            '</header>';

        // Botón flotante de WhatsApp anclado directamente a <body>
        if (!$('.whatsapp-float')) {
            var wa = document.createElement('a');
            wa.href = (C.contacto && C.contacto.whatsapp) || 'https://wa.me/584122522272?text=Hola%2C%20vengo%20de%20la%20p%C3%A1gina%20web%20de%20CVG%20ALCASA%20y%20quisiera%20informaci%C3%B3n%20sobre%20sus%20productos.';
            wa.target = '_blank';
            wa.rel = 'noopener noreferrer';
            wa.className = 'whatsapp-float';
            wa.setAttribute('aria-label', 'Contactar a CVG Alcasa por WhatsApp');
            wa.innerHTML = '<i class="fab fa-whatsapp"></i><span class="whatsapp-tooltip">Contáctanos por WhatsApp</span>';
            document.body.appendChild(wa);
        }

        initHeaderBehavior();
    }

    function initHeaderBehavior() {
        var select = $('#alcoa-lang-select');
        if (select && select.dataset.bound !== '1') {
            select.dataset.bound = '1';
            var match = document.cookie.match(/googtrans=\/es\/([^;]+)/);
            select.value = match ? match[1] : 'es';
            select.addEventListener('change', function () {
                if (typeof window.alcosaSetLanguage === 'function') {
                    window.alcosaSetLanguage(this.value);
                }
            });
        }

        var fadeEl = $('#headerScrollFade');
        var headerContainer = $('#site-header') || $('#header-container');

        if (headerContainer) {
            if (headerContainer.dataset.scrollBound !== '1') {
                headerContainer.dataset.scrollBound = '1';
                var onScroll = function () {
                    var mainNavEl = $('#mainNav');
                    if (mainNavEl && mainNavEl.classList.contains('active')) {
                        headerContainer.style.opacity = '1';
                        headerContainer.style.transform = 'translateY(0)';
                        headerContainer.style.pointerEvents = 'auto';
                        headerContainer.classList.remove('header-hidden-scroll');
                        return;
                    }

                    var scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
                    var fadeDistance = 220;
                    var ratio = Math.max(0, Math.min(1, 1 - (scrollY / fadeDistance)));
                    var shiftY = -Math.round((1 - ratio) * 115);

                    headerContainer.style.opacity = ratio.toFixed(3);
                    headerContainer.style.transform = 'translateY(' + shiftY + 'px)';
                    headerContainer.style.pointerEvents = ratio <= 0.08 ? 'none' : 'auto';
                    headerContainer.classList.toggle('header-scrolled', scrollY > 40);
                    headerContainer.classList.toggle('header-hidden-scroll', ratio <= 0.08);

                    if (fadeEl) {
                        fadeEl.style.opacity = ratio.toFixed(3);
                        fadeEl.style.transform = 'translateY(' + (-Math.round((1 - ratio) * 18)) + 'px)';
                    }
                };
                window.addEventListener('scroll', onScroll, { passive: true });
                onScroll();
            }
        }

        var menuBtn = $('#mobileMenuBtn');
        var closeBtn = $('#mobileCloseBtn');
        var mainNav = $('#mainNav');
        var overlay = $('#menuOverlay');

        if (menuBtn && mainNav && menuBtn.dataset.bound !== '1') {
            menuBtn.dataset.bound = '1';

            var abrirMenu = function () {
                mainNav.classList.add('active');
                if (overlay) overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                menuBtn.setAttribute('aria-expanded', 'true');
                var icon = $('i', menuBtn);
                if (icon) { icon.classList.remove('fa-bars'); icon.classList.add('fa-times'); }
            };

            var cerrarMenu = function () {
                mainNav.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
                document.body.style.overflow = '';
                menuBtn.setAttribute('aria-expanded', 'false');
                var icon = $('i', menuBtn);
                if (icon) { icon.classList.add('fa-bars'); icon.classList.remove('fa-times'); }
                $$('.nav-item-has-dropdown.open').forEach(function (li) { li.classList.remove('open'); });
                $$('.dropdown-menu.open').forEach(function (m) { m.classList.remove('open'); });
            };

            menuBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (mainNav.classList.contains('active')) cerrarMenu();
                else abrirMenu();
            });
            if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); cerrarMenu(); });
            if (overlay) overlay.addEventListener('click', cerrarMenu);

            mainNav.addEventListener('click', function (e) {
                var dropdownToggle = e.target.closest('.nav-link-dropdown');
                if (dropdownToggle && window.innerWidth <= 992) {
                    e.preventDefault();
                    e.stopPropagation();
                    var parentLi = dropdownToggle.closest('.nav-item-has-dropdown');
                    var submenu = parentLi ? $('.dropdown-menu', parentLi) : null;
                    if (!submenu) return;
                    var estabaAbierto = submenu.classList.contains('open');
                    $$('.nav-item-has-dropdown.open', mainNav).forEach(function (li) {
                        if (li !== parentLi) {
                            li.classList.remove('open');
                            var m = $('.dropdown-menu', li);
                            if (m) m.classList.remove('open');
                        }
                    });
                    submenu.classList.toggle('open', !estabaAbierto);
                    parentLi.classList.toggle('open', !estabaAbierto);
                    return;
                }
                var link = e.target.closest('a');
                if (link && window.innerWidth <= 992 && !link.classList.contains('nav-link-dropdown')) {
                    cerrarMenu();
                }
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 992 && mainNav.classList.contains('active')) {
                    cerrarMenu();
                }
            });
        }

        $$('.jelly-menu > li > a', mainNav).forEach(function (link) {
            if (link.dataset.activeBound === '1') return;
            link.dataset.activeBound = '1';
            link.addEventListener('click', function () {
                if (window.innerWidth > 992 || !this.classList.contains('nav-link-dropdown')) {
                    $$('.jelly-menu > li', mainNav).forEach(function (li) { li.classList.remove('active'); });
                    var parentLi = this.closest('.jelly-menu > li');
                    if (parentLi) parentLi.classList.add('active');
                }
            });
        });

        bindSmoothScrollLinks(headerContainer);
    }

    function renderFooter() {
        if (isStandaloneBackofficePage()) return;
        var f = $('#site-footer');
        if (!f || $('footer', f)) return;
        var k = C.contacto || {};
        var dir = k.direccion || 'Zona Industrial Matanzas, Avenida Fuerzas Armadas, Ciudad Guayana, Estado Bolívar, Venezuela.';
        var rif = k.rif || 'G-20110929-0';
        var email = k.email || 'comercializacion@alcasa.com.ve';
        var tel = k.telefono || '+58 (0286) 9801476 / +58 (0412) 2522272';
        var horario = k.horario || 'Lun - Vie: 7:00am – 4:00pm';

        f.innerHTML = '' +
            '<footer>' +
                '<div class="container">' +
                    '<div class="footer-grid">' +
                        '<div class="footer-col">' +
                            '<h4>CVG Aluminio del Caroní, S.A.</h4>' +
                            '<p>CVG Aluminio del Caroní, S.A. (CVG Alcasa)<br>' + esc(dir) + '</p>' +
                            '<div class="social-links">' +
                                '<a href="https://www.facebook.com/Alcasa.CVG/?locale=es_LA" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>' +
                                '<a href="https://www.instagram.com/alcasa_cvg/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>' +
                                '<a href="https://www.youtube.com/@alcasaaluminiodelcaronis.a8220" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><i class="fab fa-youtube"></i></a>' +
                                '<a href="https://www.tiktok.com/@cvg_alcasa" target="_blank" rel="noopener noreferrer" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>' +
                            '</div>' +
                        '</div>' +
                        '<div class="footer-col">' +
                            '<h4>Empresa</h4>' +
                            '<ul>' +
                                '<li><a href="/index.html#empresa" data-tab-target="tab-quienes-somos"><i class="fas fa-chevron-right"></i> Quiénes somos</a></li>' +
                                '<li><a href="/index.html#empresa" data-tab-target="tab-mision-vision"><i class="fas fa-chevron-right"></i> Misión y Visión</a></li>' +
                                '<li><a href="/index.html#empresa" data-tab-target="tab-valores"><i class="fas fa-chevron-right"></i> Valores</a></li>' +
                                '<li><a href="/index.html#oportunidades"><i class="fas fa-chevron-right"></i> Oportunidades Laborales</a></li>' +
                                '<li><a href="/prensa.html"><i class="fas fa-chevron-right"></i> Pulso Industrial CVG Alcasa</a></li>' +
                            '</ul>' +
                        '</div>' +
                        '<div class="footer-col">' +
                            '<h4>Productos</h4>' +
                            '<ul>' +
                                '<li><a href="/productos.html"><i class="fas fa-chevron-right"></i> Catálogo Completo (32)</a></li>' +
                                '<li><a href="/productos.html?linea=Fundición"><i class="fas fa-chevron-right"></i> Cilindros / Fundición</a></li>' +
                                '<li><a href="/productos.html?linea=Extrusión"><i class="fas fa-chevron-right"></i> Perfilería / Extrusión</a></li>' +
                                '<li><a href="/productos.html?linea=Laminación"><i class="fas fa-chevron-right"></i> Laminados / Laminación</a></li>' +
                                '<li><a href="/index.html#faq"><i class="fas fa-chevron-right"></i> Preguntas Frecuentes</a></li>' +
                            '</ul>' +
                        '</div>' +
                        '<div class="footer-col">' +
                            '<h4>Contacto Oficial</h4>' +
                            '<ul>' +
                                '<li><i class="fas fa-map-marker-alt" style="color: var(--alcasa-accent);"></i> Zona Industrial Matanzas, Ciudad Guayana</li>' +
                                '<li><i class="fas fa-clock" style="color: var(--alcasa-accent);"></i> ' + esc(horario) + '</li>' +
                                '<li><i class="fas fa-phone" style="color: var(--alcasa-accent);"></i> ' + esc(tel) + '</li>' +
                                '<li><i class="fas fa-envelope" style="color: var(--alcasa-accent);"></i> ' + esc(email) + '</li>' +
                                '<li><i class="fas fa-id-card" style="color: var(--alcasa-accent);"></i> RIF: ' + esc(rif) + '</li>' +
                            '</ul>' +
                        '</div>' +
                    '</div>' +
                    '<div class="footer-bottom">' +
                        '<p>&copy; ' + new Date().getFullYear() + ' - CVG Aluminio del Caroní, S.A. (CVG Alcasa). Todos los derechos reservados. | RIF: ' + esc(rif) + '</p>' +
                    '</div>' +
                '</div>' +
            '</footer>';
    }

    /* ======================================================================
       5. INTERACTIVIDAD DEL HOME (BANNER ROTATIVO, TABS, PRODUCTOS, VIDEOS, FAQ)
       ====================================================================== */
    function initHeroSlider() {
        var heroSection = $('.alcoa-hero');
        if (!heroSection) return;
        var slides = $$('.hero-slide', heroSection);
        var indicators = $$('.indicator', heroSection);
        var heroContent = $('#heroContent');
        var prevBtn = $('#heroPrevBtn');
        var nextBtn = $('#heroNextBtn');
        var currentSlide = 0;
        var slideInterval = null;

        var slideData = [
            { title: 'Laminación', text: 'Nuestros productos', cat: 'Laminación', link: '#productos' },
            { title: 'Fundición', text: 'Nuestros productos', cat: 'Fundición', link: '#productos' },
            { title: 'Extrusión', text: 'Nuestros productos', cat: 'Extrusión', link: '#productos' }
        ];

        function updateHeroContent(index) {
            if (!heroContent) return;
            var data = slideData[index] || slideData[0];
            heroContent.innerHTML =
                '<h2>' + esc(data.title) + '</h2>' +
                '<p>' + esc(data.text) + '</p>' +
                '<a href="' + esc(data.link) + '" data-cat="' + esc(data.cat) + '" class="hero-btn">Acceder <i class="fas fa-arrow-right"></i></a>';
            bindSmoothScrollLinks(heroContent);
            var btn = $('.hero-btn', heroContent);
            if (btn) {
                btn.addEventListener('click', function () {
                    var targetCat = this.getAttribute('data-cat');
                    var filterBtn = $('.product-filter-btn[data-filter="' + targetCat + '"]');
                    if (filterBtn) filterBtn.click();
                });
            }
        }

        function showSlide(index) {
            if (!slides.length) return;
            currentSlide = (index + slides.length) % slides.length;
            slides.forEach(function (slide, i) {
                slide.classList.toggle('active', i === currentSlide);
            });
            indicators.forEach(function (ind, i) {
                ind.classList.toggle('active', i === currentSlide);
            });
            updateHeroContent(currentSlide);
        }

        function nextSlide() { showSlide(currentSlide + 1); resetInterval(); }
        function prevSlide() { showSlide(currentSlide - 1); resetInterval(); }
        function resetInterval() {
            if (slideInterval) clearInterval(slideInterval);
            slideInterval = setInterval(function () { showSlide(currentSlide + 1); }, 6000);
        }

        showSlide(0);
        resetInterval();

        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
        indicators.forEach(function (ind, i) {
            ind.addEventListener('click', function () { showSlide(i); resetInterval(); });
        });

        heroSection.addEventListener('mouseenter', function () { if (slideInterval) clearInterval(slideInterval); });
        heroSection.addEventListener('mouseleave', resetInterval);

        var touchStartX = 0;
        heroSection.addEventListener('touchstart', function (e) { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
        heroSection.addEventListener('touchend', function (e) {
            var diff = touchStartX - e.changedTouches[0].screenX;
            if (Math.abs(diff) > 40) {
                if (diff > 0) nextSlide();
                else prevSlide();
            }
        }, { passive: true });
    }

    function initEmpresaTabs() {
        var empresaTabs = $$('.empresa-tab-btn');
        var empresaPanes = $$('.empresa-tab-pane');
        empresaTabs.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var targetTab = this.getAttribute('data-tab');
                if (!targetTab) return;
                empresaTabs.forEach(function (b) { b.classList.remove('active'); });
                empresaPanes.forEach(function (p) { p.classList.remove('active'); });
                this.classList.add('active');
                var activePane = document.getElementById(targetTab);
                if (activePane) activePane.classList.add('active');
            });
        });

        // Sincronizar textos de Misión y Visión desde contenido.json si están definidos
        if (C.empresa) {
            var misionEl = $('#mision');
            var visionEl = $('#vision');
            if (misionEl && C.empresa.mision && C.empresa.mision.indexOf('PENDIENTE') === -1) {
                misionEl.textContent = C.empresa.mision;
            }
            if (visionEl && C.empresa.vision && C.empresa.vision.indexOf('PENDIENTE') === -1) {
                visionEl.textContent = C.empresa.vision;
            }
        }
    }

    function initProductosCatalogo() {
        var fichaPanel = $('#productFichaPanel');
        var fichaCloseBtn = $('#fichaCloseBtn');
        var fichaBtnCerrarFooter = $('#fichaBtnCerrarFooter');
        var fichaBtnCotizar = $('#fichaBtnCotizar');

        if (fichaPanel && fichaPanel.parentElement !== document.body) {
            document.body.appendChild(fichaPanel);
        }

        var defaultSpecs = {
            0: {
                orden: 1,
                linea: 'Fundición',
                nombre: 'Cilindros Tochos',
                nombre_en: 'Billet Cylinders',
                img: '/assets/img/productos/cilindros-tochos.jpg',
                desc: 'Cilindros de aluminio primario homogeneizados en aleación 6063/S6063 temple T5 y diámetro 7", cortados a medida para alimentar prensas de extrusión con máxima precisión metalúrgica.',
                spec: 'Aleación: 6063/S6063 | Temple: T5 | Diámetro: 7" | Longitud: Cortados a medida | Homogenizado',
                titulo: 'Cilindros Tochos',
                aleacion: 'Serie AA 6063 / S6063 — Temple T5 (Homogenizado)',
                pureza: 'Aluminio primario aleado con Silicio (Si) y Magnesio (Mg) homogeneizado en hornos de tratamiento térmico',
                presentaciones: 'Diámetro 7" (177,8 mm) | Longitud: Cortados a medida según requerimiento de prensa',
                propiedades: 'Estructura cristalina uniforme, alta fluidez de extrusión y excelente respuesta al anodizado',
                normas: 'Catálogo Oficial CVG Alcasa 2025 / COVENIN 284 / ASTM B221',
                usos: 'Materia prima directa para prensas extrusoras de perfilería arquitectónica, estructural y tubular.',
                cotizar: 'Cilindros Tochos (Fundición)',
                codigos_tabla: [
                    { codigo: 'CIL-6063-7', detalle: 'Aleación 6063/S6063 · Temple T5 · Diámetro 7" (177,8 mm)', bulto: 'Cortados a medida', peso: 'Homogenizado' }
                ]
            },
            28: {
                orden: 29,
                linea: 'Laminación',
                nombre: 'Rollo de aluminio liso o stucco',
                nombre_en: 'Smooth or Stucco Aluminum Roll',
                img: '/assets/img/productos/rollo-aluminio-liso-stucco.jpg',
                desc: 'Bobina de aluminio de bajo espesor laminada en frío con superficie lisa brillante o grabada stucco de calibre uniforme, ideal para líneas de acanalado de techos, aislamiento térmico, refrigeración, ductería y empaques.',
                spec: 'Aleación: 1050, 1100 y 3003 | Temple: F/H - O / H-14 / H-16 / H-18 | Espesor: de 0.3 hasta 0.9 mm | Ancho: 1220 mm',
                titulo: 'Rollo de aluminio liso o stucco',
                aleacion: 'Series AA 1050, AA 1100 y AA 3003 — Temples F/H - O / H-14 / H-16 / H-18',
                pureza: 'Bobina de aluminio de bajo espesor laminada en frío con superficie lisa brillante o grabada stucco',
                presentaciones: 'Espesor: de 0.3 hasta 0.9 mm | Ancho estándar: 1220 mm | Acabado: Liso y stucco',
                propiedades: 'Excelente formabilidad para perfiladoras de techos, aislamiento térmico/acústico y embutido',
                normas: 'Catálogo Oficial CVG Alcasa 2025 — Laminados pág. 11 / ASTM B209 / COVENIN 1324',
                usos: 'Fabricación de láminas acanaladas para techos, recubrimiento de tuberías térmicas, refrigeración y envases.',
                cotizar: 'Rollo de aluminio liso o stucco (Laminación)',
                codigos_tabla: [
                    { codigo: 'ROL-LISO-STUCCO', detalle: 'Aleaciones 1050 / 1100 / 3003 · Espesor 0.3 a 0.9 mm · Ancho 1220 mm', bulto: 'Bobina industrial', peso: 'Acabado Liso o Stucco' }
                ]
            }
        };

        function cerrarFichaModal() {
            if (!fichaPanel) return;
            fichaPanel.style.display = 'none';
            document.body.style.overflow = '';
        }

        function abrirFichaPorIndice(idx) {
            var prod = C.productos && C.productos[idx];
            var def = defaultSpecs[idx] || defaultSpecs[0];
            var f = (prod && prod.ficha) || {};
            var ordenNum = (prod && prod.orden) || def.orden || (idx + 1);
            var numStr = '#' + String(ordenNum).padStart(2, '0');
            var lineaStr = (prod && prod.linea) || def.linea || 'Catálogo 2025';
            var nombreProd = (prod && prod.nombre) || def.nombre || def.titulo;
            var nombreEn = (prod && prod.nombre_en) || def.nombre_en || 'CVG Alcasa Official Product';
            var imgProd = (prod && prod.img) || def.img || '/assets/img/productos/cilindros-tochos.jpg';
            var descProd = (prod && prod.desc) || def.desc || '';
            var specProd = (prod && prod.spec) || def.spec || '';
            var codigosTabla = (prod && Array.isArray(prod.codigos_tabla) && prod.codigos_tabla.length)
                ? prod.codigos_tabla
                : (def.codigos_tabla || []);

            var data = {
                badgeOrden: numStr + ' · Planta de ' + lineaStr,
                nombreEn: nombreEn,
                titulo: nombreProd,
                img: imgProd,
                desc: descProd,
                spec: specProd,
                aleacion: f.aleacion || def.aleacion,
                pureza: f.pureza || def.pureza,
                presentaciones: f.presentaciones || def.presentaciones,
                propiedades: f.propiedades || def.propiedades,
                normas: f.normas || def.normas,
                usos: f.usos || def.usos,
                cotizar: (prod && (prod.cotizar || prod.nombre)) || def.cotizar
            };

            if (fichaPanel && data) {
                var badgeOrdenEl = $('#fichaBadgeOrden');
                var nombreEnEl = $('#fichaNombreIngles');
                var tituloEl = $('#fichaTituloTexto');
                var aleacionEl = $('#fichaAleacion');
                var aleacionBoxEl = $('#fichaAleacionBox');
                var imgEl = $('#fichaImagenProducto');
                var descEl = $('#fichaDescTexto');
                var specResumenEl = $('#fichaSpecResumen');
                var purezaEl = $('#fichaPureza');
                var presentacionesEl = $('#fichaPresentaciones');
                var propiedadesEl = $('#fichaPropiedades');
                var normasEl = $('#fichaNormas');
                var usosEl = $('#fichaUsos');
                var tablaWrapEl = $('#fichaTablaCodigosWrap');

                if (badgeOrdenEl) badgeOrdenEl.textContent = data.badgeOrden;
                if (nombreEnEl) nombreEnEl.textContent = data.nombreEn;
                if (tituloEl) tituloEl.textContent = data.titulo;
                if (aleacionEl) aleacionEl.textContent = data.aleacion;
                if (aleacionBoxEl) aleacionBoxEl.textContent = data.aleacion;
                if (imgEl) {
                    imgEl.src = data.img;
                    imgEl.alt = data.titulo + ' - CVG Alcasa';
                }
                if (descEl) descEl.textContent = data.desc;
                if (specResumenEl) {
                    specResumenEl.innerHTML = '<i class="fas fa-microchip"></i> ' + esc(data.spec);
                }
                if (purezaEl) purezaEl.textContent = data.pureza;
                if (presentacionesEl) presentacionesEl.textContent = data.presentaciones;
                if (propiedadesEl) propiedadesEl.textContent = data.propiedades;
                if (normasEl) normasEl.textContent = data.normas;
                if (usosEl) usosEl.textContent = data.usos;
                if (fichaBtnCotizar) fichaBtnCotizar.setAttribute('data-producto', data.cotizar);

                if (tablaWrapEl) {
                    if (codigosTabla.length) {
                        tablaWrapEl.innerHTML = '' +
                            '<div class="ficha-section-subtitle" style="margin-bottom:10px;">' +
                                '<i class="fas fa-table-list"></i> Tabla de Códigos, Dimensiones, Piezas por Bulto y Peso Oficial' +
                            '</div>' +
                            '<div class="catalog-item-table-wrap ficha-modal-table-wrap">' +
                                '<table class="catalog-item-table">' +
                                    '<thead>' +
                                        '<tr>' +
                                            '<th>Código / Referencia</th>' +
                                            '<th>Dimensiones / Especificación</th>' +
                                            '<th>Piezas / Bulto</th>' +
                                            '<th>Peso / Acabado</th>' +
                                        '</tr>' +
                                    '</thead>' +
                                    '<tbody>' +
                                        codigosTabla.map(function (r) {
                                            return '<tr>' +
                                                '<td><strong>' + esc(r.codigo || '-') + '</strong></td>' +
                                                '<td>' + esc(r.detalle || '-') + '</td>' +
                                                '<td>' + esc(r.bulto || '-') + '</td>' +
                                                '<td>' + esc(r.peso || '-') + '</td>' +
                                            '</tr>';
                                        }).join('') +
                                    '</tbody>' +
                                '</table>' +
                            '</div>';
                    } else {
                        tablaWrapEl.innerHTML = '';
                    }
                }

                fichaPanel.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function cotizarProducto(producto) {
            var interesSelect = $('#contacto-interes') || $('#interes');
            var comentarioArea = $('#contacto-comentario') || $('textarea[name="comentario"]');

            if (interesSelect && producto) {
                var optionFound = false;
                for (var i = 0; i < interesSelect.options.length; i++) {
                    if (interesSelect.options[i].value === producto || interesSelect.options[i].text.indexOf(producto) !== -1) {
                        interesSelect.selectedIndex = i;
                        optionFound = true;
                        break;
                    }
                }
                if (!optionFound) {
                    var newOpt = new Option(producto, producto, true, true);
                    interesSelect.add(newOpt);
                }
            }

            if (comentarioArea && producto) {
                comentarioArea.value = producto === 'Registro de Proveedor'
                    ? 'Hola, deseo recibir información y requisitos para el Registro Oficial de Proveedores de CVG Alcasa.'
                    : 'Hola, deseo cotizar y conocer disponibilidad del producto oficial: ' + producto + '.';
            }

            var formSection = $('#contacto');
            if (formSection) {
                var headerEl = $('#site-header') || $('#header-container');
                var headerOffset = headerEl ? headerEl.offsetHeight + 12 : 105;
                var offsetPosition = formSection.getBoundingClientRect().top + (window.pageYOffset || document.documentElement.scrollTop || 0) - headerOffset;
                smoothScrollTo(offsetPosition, 520);
                var nombreInput = $('#contacto-nombre') || $('input[name="nombre"]', formSection);
                if (nombreInput) setTimeout(function () { nombreInput.focus(); }, 550);
            }
        }

        function bindProductActionButtons(root) {
            $$('.btn-catalog-ficha', root || document).forEach(function (btn) {
                if (btn.dataset.fichaBound === '1') return;
                btn.dataset.fichaBound = '1';
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var idx = parseInt(this.getAttribute('data-product-index') || '0', 10);
                    abrirFichaPorIndice(idx);
                });
            });

            $$('.btn-catalog-cotizar', root || document).forEach(function (btn) {
                if (btn.dataset.cotizarBound === '1') return;
                btn.dataset.cotizarBound = '1';
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var prodName = this.getAttribute('data-producto');
                    cerrarFichaModal();
                    cotizarProducto(prodName);
                });
            });
        }

        bindProductActionButtons(document);

        if (fichaCloseBtn && fichaPanel) {
            fichaCloseBtn.addEventListener('click', cerrarFichaModal);
        }
        if (fichaBtnCerrarFooter && fichaPanel) {
            fichaBtnCerrarFooter.addEventListener('click', cerrarFichaModal);
        }
        if (fichaPanel) {
            fichaPanel.addEventListener('click', function (e) {
                if (e.target === fichaPanel) cerrarFichaModal();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && fichaPanel.style.display !== 'none') {
                    cerrarFichaModal();
                }
            });
        }

        // Renderizado del Catálogo Completo Ordenado (#01 a #32) en /productos.html
        var orderedGrid = $('#catalogoOrderedGrid');
        if (orderedGrid && Array.isArray(C.productos) && C.productos.length) {
            var countBar = $('#catalogoCountBar');
            var searchInput = $('#catalogoSearchInput');
            var filterBtns = $$('#catalogoFilterBar .product-filter-btn');
            var activeFilter = 'all';
            var currentQuery = '';

            // Leer parámetro ?linea=Fundición | Extrusión | Laminación de la URL
            try {
                var params = new URLSearchParams(window.location.search);
                var lineaParam = params.get('linea');
                if (lineaParam) {
                    activeFilter = lineaParam;
                    filterBtns.forEach(function (b) {
                        b.classList.toggle('active', b.getAttribute('data-filter') === activeFilter);
                    });
                }
            } catch (e) {}

            function renderOrderedCatalog() {
                var lista = C.productos.slice().sort(function (a, b) {
                    return (a.orden || 0) - (b.orden || 0);
                });

                var filtrados = lista.filter(function (p) {
                    var matchCat = (activeFilter === 'all') || (p.linea === activeFilter);
                    if (!matchCat) return false;
                    if (!currentQuery) return true;
                    var q = currentQuery.toLowerCase();
                    var codigosStr = Array.isArray(p.codigos_tabla)
                        ? p.codigos_tabla.map(function (c) { return (c.codigo || '') + ' ' + (c.detalle || ''); }).join(' ')
                        : '';
                    var haystack = [
                        p.nombre, p.nombre_en, p.linea, p.badge, p.desc, p.spec, codigosStr
                    ].join(' ').toLowerCase();
                    return haystack.indexOf(q) !== -1;
                });

                if (countBar) {
                    countBar.innerHTML = 'Mostrando <strong>' + filtrados.length + '</strong> de <strong>' + lista.length + ' productos oficiales</strong> en orden correlativo del Catálogo CVG Alcasa 2025';
                }

                if (!filtrados.length) {
                    orderedGrid.innerHTML = '<div class="catalogo-empty-state"><i class="fas fa-box-open"></i><p>No se encontraron productos que coincidan con tu búsqueda.</p></div>';
                    return;
                }

                orderedGrid.innerHTML = filtrados.map(function (p) {
                    var realIdx = C.productos.indexOf(p);
                    var numStr = '#' + String(p.orden || (realIdx + 1)).padStart(2, '0');
                    var DestacadoTag = p.destacado_home
                        ? '<span class="catalog-item-featured-tag"><i class="fas fa-star"></i> Destacado en Inicio</span>'
                        : '';

                    var tablaRows = '';
                    if (Array.isArray(p.codigos_tabla) && p.codigos_tabla.length) {
                        tablaRows = '' +
                            '<div class="catalog-item-table-wrap">' +
                                '<table class="catalog-item-table">' +
                                    '<thead>' +
                                        '<tr>' +
                                            '<th>Código / Ref.</th>' +
                                            '<th>Dimensiones / Especificación</th>' +
                                            '<th>Bulto</th>' +
                                            '<th>Peso / Acabado</th>' +
                                        '</tr>' +
                                    '</thead>' +
                                    '<tbody>' +
                                        p.codigos_tabla.map(function (r) {
                                            return '<tr>' +
                                                '<td><strong>' + esc(r.codigo) + '</strong></td>' +
                                                '<td>' + esc(r.detalle) + '</td>' +
                                                '<td>' + esc(r.bulto) + '</td>' +
                                                '<td>' + esc(r.peso) + '</td>' +
                                            '</tr>';
                                        }).join('') +
                                    '</tbody>' +
                                '</table>' +
                            '</div>';
                    }

                    return '' +
                        '<article class="catalog-ordered-card" data-category="' + esc(p.linea) + '" id="prod-' + esc(p.id || p.orden) + '">' +
                            '<div class="catalog-ordered-img-box">' +
                                '<div class="catalog-ordered-badges">' +
                                    '<span class="catalog-ordered-num">' + esc(numStr) + '</span>' +
                                    '<span class="catalog-ordered-linea">' + esc(p.linea) + '</span>' +
                                '</div>' +
                                DestacadoTag +
                                '<img loading="lazy" src="' + esc(p.imagen || PH) + '" alt="' + esc(p.nombre) + ' - Producto Final CVG Alcasa" class="catalog-ordered-img" onerror="this.onerror=null;this.src=\'' + PH + '\';">' +
                            '</div>' +
                            '<div class="catalog-ordered-body">' +
                                '<div class="catalog-ordered-titles">' +
                                    '<span class="catalog-ordered-en">' + esc(p.nombre_en || p.badge) + '</span>' +
                                    '<h3>' + esc(p.nombre) + '</h3>' +
                                '</div>' +
                                '<p class="catalog-ordered-desc">' + esc(p.desc) + '</p>' +
                                '<div class="catalog-ordered-spec-pill">' +
                                    '<i class="fas fa-microchip"></i> ' + esc(p.spec) +
                                '</div>' +
                                tablaRows +
                                '<div class="catalog-ordered-actions">' +
                                    '<button type="button" class="btn-catalog-cotizar" data-producto="' + esc(p.cotizar || p.nombre) + '">' +
                                        '<i class="fas fa-file-invoice"></i> Cotizar' +
                                    '</button>' +
                                    '<button type="button" class="btn-catalog-ficha" data-product-index="' + realIdx + '">' +
                                        '<i class="fas fa-file-lines"></i> Ficha Técnica' +
                                    '</button>' +
                                '</div>' +
                            '</div>' +
                        '</article>';
                }).join('');

                bindProductActionButtons(orderedGrid);
            }

            filterBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    activeFilter = this.getAttribute('data-filter') || 'all';
                    filterBtns.forEach(function (b) { b.classList.remove('active'); });
                    this.classList.add('active');
                    renderOrderedCatalog();
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    currentQuery = (this.value || '').trim();
                    renderOrderedCatalog();
                });
            }

            renderOrderedCatalog();
        }
    }

    function initFAQ() {
        var faqItems = $$('.faq-item');
        faqItems.forEach(function (item) {
            var question = $('.faq-question', item);
            if (question) {
                question.addEventListener('click', function () {
                    var isOpen = item.classList.contains('open');
                    faqItems.forEach(function (i) { i.classList.remove('open'); });
                    if (!isOpen) item.classList.add('open');
                });
            }
        });
    }

    function initGaleriaVideos() {
        var panel = $('#videoPlayerPanel');
        var closeBtn = $('#videoPanelCloseBtn');
        var closeFooterBtn = $('#videoPanelCloseFooterBtn');
        var titleEl = $('#videoPanelTitle');
        var badgeEl = $('#videoPanelBadge');
        var descEl = $('#videoPanelDesc');
        var bodyEl = $('#videoPanelMedia');

        if (!panel) return;
        if (panel.parentElement !== document.body) {
            document.body.appendChild(panel);
        }

        function cerrarVideoModal() {
            if (bodyEl) bodyEl.innerHTML = '';
            panel.style.display = 'none';
            document.body.style.overflow = '';
        }

        function bindVideoTriggers(root) {
            $$('.btn-ver-video', root || document).forEach(function (btn) {
                if (btn.dataset.videoBound === '1') return;
                btn.dataset.videoBound = '1';
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var idx = parseInt(this.getAttribute('data-video-index') || '0', 10);
                    var v = (C.videos && C.videos[idx]) || {};
                    var titulo = v.titulo || this.getAttribute('data-video-title') || 'Proceso Productivo CVG Alcasa';
                    var badge = v.badge || this.getAttribute('data-video-badge') || 'Galería de Procesos';
                    var desc = v.desc || this.getAttribute('data-video-desc') || '';
                    var poster = v.poster || this.getAttribute('data-video-poster') || '/assets/img/hero-laminacion.jpg';
                    var src = v.src || this.getAttribute('data-video-src') || '';

                    if (titleEl) titleEl.textContent = titulo;
                    if (badgeEl) badgeEl.textContent = badge;
                    if (descEl) descEl.textContent = desc;

                    if (bodyEl) {
                        if (src) {
                            bodyEl.innerHTML = '<video controls autoplay playsinline poster="' + esc(poster) + '" src="' + esc(src) + '" style="width:100%;max-height:500px;border-radius:12px;background:#000;border:1px solid #cfe0f0;display:block;"></video>';
                        } else {
                            bodyEl.innerHTML = '' +
                                '<div style="position:relative;border-radius:12px;overflow:hidden;border:1px solid #cfe0f0;background:#000;">' +
                                    '<img src="' + esc(poster) + '" alt="' + esc(titulo) + '" style="width:100%;max-height:420px;object-fit:cover;opacity:0.55;">' +
                                    '<div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;text-align:center;background:rgba(2,34,133,0.45);">' +
                                        '<i class="fas fa-film" style="font-size:3rem;color:var(--alcasa-accent-light);margin-bottom:12px;"></i>' +
                                        '<strong style="color:#fff;font-size:1.2rem;margin-bottom:6px;">' + esc(titulo) + '</strong>' +
                                        '<span style="color:#c7d1da;font-size:0.92rem;max-width:520px;">Archivo audiovisual del proceso productivo.</span>' +
                                    '</div>' +
                                '</div>';
                        }
                    }

                    panel.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                });
            });
        }

        bindVideoTriggers(document);

        if (closeBtn) closeBtn.addEventListener('click', cerrarVideoModal);
        if (closeFooterBtn) closeFooterBtn.addEventListener('click', cerrarVideoModal);
        panel.addEventListener('click', function (e) {
            if (e.target === panel) cerrarVideoModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && panel.style.display !== 'none') {
                cerrarVideoModal();
            }
        });
    }

    function initPaginaGaleria() {
        var fotosGrid = $('#galeriaFotosGrid');
        var videosSection = $('#galeriaVideosSection');
        var fotosSection = $('#galeriaFotosSection');
        var videosList = $('#galeriaVideosList');
        var filterBtns = $$('#galeriaFilterBar .catalogo-filter-btn');
        var searchInput = $('#galeriaSearchInput');
        var countBar = $('#galeriaCountBar');
        var videosBadge = $('#galeriaVideosCountBadge');
        var fotosBadge = $('#galeriaFotosCountBadge');

        if (!fotosGrid && !videosList) return;

        var defaultFotos = [
            { id: 'lam-1', categoria: 'Planta de Laminación', titulo: 'Bobinas de Aluminio Laminado Listas para Despacho', desc: 'Rollos de aluminio primario laminados en frío y caliente con acabado superficial de alta pureza para la industria nacional.', img: '/assets/img/galeria/laminacion-bobinas.jpg' },
            { id: 'lam-2', categoria: 'Planta de Laminación', titulo: 'Línea de Colada Continua de Cintas de Aluminio', desc: 'Solidificación directa de aluminio fundido en cintas continuas para alimentación de los trenes de laminación.', img: '/assets/img/galeria/laminacion-colada-continua.jpg' },
            { id: 'lam-3', categoria: 'Planta de Laminación', titulo: 'Tren de Laminación en Caliente Clecim-Cosim', desc: 'Reducción de espesor de planchones de aluminio mediante pases controlados de alta precisión.', img: '/assets/img/galeria/laminacion-tren-caliente.jpg' },
            { id: 'lam-4', categoria: 'Planta de Laminación', titulo: 'Laminador en Frío de Alta Velocidad', desc: 'Operación del complejo laminador para bobinas delgadas y foil industrial.', img: '/assets/img/hero-laminacion.jpg' },
            { id: 'fun-1', categoria: 'Planta de Fundición', titulo: 'Hornos de Fusión y Retención de Aluminio Primario', desc: 'Preparación metalúrgica de aleaciones serie 1000, 3000 y 6063 a temperaturas controladas.', img: '/assets/img/galeria/fundicion-horno.jpg' },
            { id: 'fun-2', categoria: 'Planta de Fundición', titulo: 'Mesa de Colada Vertical de Cilindros Tochos', desc: 'Fundición semicontinua de cilindros de extrusión en aleación AA 6063.', img: '/assets/img/galeria/fundicion-colada.jpg' },
            { id: 'fun-3', categoria: 'Planta de Fundición', titulo: 'Lotes de Cilindros Tochos Homogenizados 6063-T5', desc: 'Cilindros de 7 pulgadas inspeccionados por ultrasonido y listos para prensas de extrusión.', img: '/assets/img/galeria/fundicion-cilindros.jpg' },
            { id: 'fun-4', categoria: 'Planta de Fundición', titulo: 'Operaciones en Nave de Fundición Matanzas', desc: 'Control de colada y aseguramiento de calidad metalúrgica por nuestra clase trabajadora.', img: '/assets/img/hero-fundicion.jpg' },
            { id: 'ext-1', categoria: 'Planta de Extrusión', titulo: 'Prensa Extrusora de Perfilería Industrial', desc: 'Transformación termomecánica de cilindros tochos 6063 en perfiles estructurales y arquitectónicos.', img: '/assets/img/galeria/extrusion-prensa.jpg' },
            { id: 'ext-2', categoria: 'Planta de Extrusión', titulo: 'Perfiles Tubulares y Estructurales Extruidos', desc: 'Lotes de tubería, ángulos y perfiles serie Caroní en mesa de enfriamiento y estirado.', img: '/assets/img/galeria/extrusion-perfiles-1.jpg' },
            { id: 'ext-3', categoria: 'Planta de Extrusión', titulo: 'Control Dimensional de Perfilería de Aluminio', desc: 'Verificación de tolerancias milimétricas según normas COVENIN y catálogo técnico CVG Alcasa.', img: '/assets/img/galeria/extrusion-perfiles-2.jpg' },
            { id: 'ext-4', categoria: 'Planta de Extrusión', titulo: 'Línea de Extrusión de 7 Pulgadas', desc: 'Capacidad instalada para abastecer al sector construcción, cerramientos y manufactura nacional.', img: '/assets/img/hero-extrusion.jpg' },
            { id: 'soc-1', categoria: 'Compromiso Social', titulo: 'Clase Trabajadora de CVG Alcasa en Operaciones', desc: 'Talento humano técnico y obrero comprometido con la soberanía productiva del sector aluminio.', img: '/assets/img/galeria/social-trabajadores.jpg' },
            { id: 'soc-2', categoria: 'Compromiso Social', titulo: 'Planta de Pintura y Acabados Industriales', desc: 'Instalaciones de recubrimiento y preparación superficial de láminas y perfiles de aluminio.', img: '/assets/img/galeria/social-planta-pintura.jpg' },
            { id: 'soc-3', categoria: 'Compromiso Social', titulo: 'Jornadas de Atención Social y Bienestar Integral', desc: 'Despliegue de salud, recreación y dotación de ayudas técnicas para trabajadores y comunidades de Guayana.', img: '/assets/img/galeria/social-jornada.jpg' }
        ];

        var fotosList = (Array.isArray(C.galeria_fotos) && C.galeria_fotos.length) ? C.galeria_fotos : defaultFotos;
        var activeFilter = 'all';
        var currentQuery = '';
        var currentFilteredFotos = fotosList.slice();

        // Mostrar siempre toda la galería completa salvo que exista barra de filtros activa
        if (filterBtns.length) {
            try {
                var params = new URLSearchParams(window.location.search);
                var catParam = params.get('cat');
                var tipoParam = params.get('tipo');
                if (tipoParam === 'videos' || tipoParam === 'fotos') {
                    activeFilter = tipoParam;
                } else if (catParam) {
                    activeFilter = catParam;
                }
                if (activeFilter !== 'all') {
                    filterBtns.forEach(function (b) {
                        b.classList.toggle('active', b.getAttribute('data-filter') === activeFilter);
                    });
                }
            } catch (e) {}
        }

        // Lightbox de fotografías
        var lbModal = $('#galeriaLightboxModal');
        if (lbModal && lbModal.parentElement !== document.body) {
            document.body.appendChild(lbModal);
        }
        var lbCloseBtn = $('#lightboxCloseBtn');
        var lbCloseFooterBtn = $('#lightboxCloseFooterBtn');
        var lbPrevBtn = $('#lightboxPrevBtn');
        var lbNextBtn = $('#lightboxNextBtn');
        var lbImg = $('#lightboxImage');
        var lbTitle = $('#lightboxTitle');
        var lbCat = $('#lightboxCatBadge');
        var lbCounter = $('#lightboxCounter');
        var lbDesc = $('#lightboxDesc');
        var lbRawBtn = $('#lightboxOpenRawBtn');
        var currentPhotoIndex = 0;

        function mostrarFotoLightbox(idx) {
            if (!lbModal || !currentFilteredFotos.length) return;
            if (idx < 0) idx = currentFilteredFotos.length - 1;
            if (idx >= currentFilteredFotos.length) idx = 0;
            currentPhotoIndex = idx;
            var f = currentFilteredFotos[currentPhotoIndex];
            if (lbImg) {
                lbImg.src = f.img;
                lbImg.alt = f.titulo;
            }
            if (lbTitle) lbTitle.textContent = f.titulo;
            if (lbCat) lbCat.textContent = f.categoria;
            if (lbCounter) lbCounter.textContent = 'Fotografía ' + (currentPhotoIndex + 1) + ' de ' + currentFilteredFotos.length;
            if (lbDesc) lbDesc.textContent = f.desc;
            if (lbRawBtn) lbRawBtn.href = f.img;
            lbModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function cerrarLightbox() {
            if (!lbModal) return;
            lbModal.style.display = 'none';
            document.body.style.overflow = '';
        }

        if (lbCloseBtn) lbCloseBtn.addEventListener('click', cerrarLightbox);
        if (lbCloseFooterBtn) lbCloseFooterBtn.addEventListener('click', cerrarLightbox);
        if (lbPrevBtn) lbPrevBtn.addEventListener('click', function () { mostrarFotoLightbox(currentPhotoIndex - 1); });
        if (lbNextBtn) lbNextBtn.addEventListener('click', function () { mostrarFotoLightbox(currentPhotoIndex + 1); });
        if (lbModal) {
            lbModal.addEventListener('click', function (e) {
                if (e.target === lbModal) cerrarLightbox();
            });
            document.addEventListener('keydown', function (e) {
                if (lbModal.style.display === 'none') return;
                if (e.key === 'Escape') cerrarLightbox();
                if (e.key === 'ArrowLeft') mostrarFotoLightbox(currentPhotoIndex - 1);
                if (e.key === 'ArrowRight') mostrarFotoLightbox(currentPhotoIndex + 1);
            });
        }

        function renderGaleriaCompleta() {
            var q = currentQuery.toLowerCase();
            var showVideosSection = (activeFilter !== 'fotos');
            var showFotosSection = (activeFilter !== 'videos');

            // 1. Filtrar tarjetas de video
            var videoCards = $$('.galeria-video-card', videosList || document);
            var visibleVideos = 0;
            videoCards.forEach(function (card) {
                var cat = card.getAttribute('data-categoria') || '';
                var text = (card.textContent || '').toLowerCase();
                var matchCat = (activeFilter === 'all' || activeFilter === 'videos' || cat === activeFilter);
                var matchQ = !q || text.indexOf(q) !== -1 || cat.toLowerCase().indexOf(q) !== -1;
                var show = showVideosSection && matchCat && matchQ;
                card.style.display = show ? '' : 'none';
                if (show) visibleVideos++;
            });

            if (videosSection) {
                videosSection.style.display = (showVideosSection && visibleVideos > 0) ? '' : 'none';
            }
            if (videosBadge) {
                videosBadge.textContent = visibleVideos + (visibleVideos === 1 ? ' Video Disponible' : ' Videos Disponibles');
            }

            // 2. Filtrar y renderizar fotografías clasificadas
            currentFilteredFotos = fotosList.filter(function (f) {
                if (!showFotosSection) return false;
                var matchCat = (activeFilter === 'all' || activeFilter === 'fotos' || f.categoria === activeFilter);
                if (!matchCat) return false;
                if (!q) return true;
                var haystack = (f.titulo + ' ' + f.desc + ' ' + f.categoria).toLowerCase();
                return haystack.indexOf(q) !== -1;
            });

            if (fotosSection) {
                fotosSection.style.display = showFotosSection ? '' : 'none';
            }
            if (fotosBadge) {
                fotosBadge.textContent = currentFilteredFotos.length + (currentFilteredFotos.length === 1 ? ' Fotografía Clasificada' : ' Fotografías Clasificadas');
            }

            if (fotosGrid) {
                if (!currentFilteredFotos.length) {
                    fotosGrid.innerHTML = '<div class="catalogo-empty-state"><i class="fas fa-camera"></i><p>No se encontraron fotografías en esta categoría o búsqueda.</p></div>';
                } else {
                    fotosGrid.innerHTML = currentFilteredFotos.map(function (f, idx) {
                        return '' +
                            '<article class="galeria-foto-card" data-foto-index="' + idx + '">' +
                                '<div class="galeria-foto-thumb-wrap" data-foto-index="' + idx + '" title="Clic para ampliar fotografía">' +
                                    '<img src="' + esc(f.img) + '" alt="' + esc(f.titulo) + '" class="galeria-foto-img" loading="lazy">' +
                                    '<span class="galeria-foto-cat-badge"><i class="fas fa-tag"></i> ' + esc(f.categoria) + '</span>' +
                                    '<div class="galeria-foto-zoom-overlay"><i class="fas fa-magnifying-glass-plus"></i> Ver Foto Completa</div>' +
                                '</div>' +
                                '<div class="galeria-foto-body">' +
                                    '<h4>' + esc(f.titulo) + '</h4>' +
                                    '<p>' + esc(f.desc) + '</p>' +
                                    '<button type="button" class="btn-galeria-ampliar" data-foto-index="' + idx + '">' +
                                        '<i class="fas fa-expand"></i> Ampliar Fotografía' +
                                    '</button>' +
                                '</div>' +
                            '</article>';
                    }).join('');

                    $$('[data-foto-index]', fotosGrid).forEach(function (el) {
                        el.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            var i = parseInt(this.getAttribute('data-foto-index') || '0', 10);
                            mostrarFotoLightbox(i);
                        });
                    });
                }
            }

            if (countBar) {
                countBar.innerHTML = 'Mostrando <strong>' + visibleVideos + ' videos completos</strong> y <strong>' + currentFilteredFotos.length + ' fotografías clasificadas</strong> (' + (activeFilter === 'all' ? 'Todas las áreas' : esc(activeFilter)) + ')';
            }
        }

        filterBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                filterBtns.forEach(function (b) { b.classList.remove('active'); });
                this.classList.add('active');
                activeFilter = this.getAttribute('data-filter') || 'all';
                renderGaleriaCompleta();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                currentQuery = (this.value || '').trim();
                renderGaleriaCompleta();
            });
        }

        renderGaleriaCompleta();
    }

    /* ======================================================================
       6. PULSO INDUSTRIAL CVG ALCASA (TOP 10 NOTICIAS CON MINIATURA, RESUMEN, FECHA Y HORA)
       ====================================================================== */
    function separarFechaHora(fechaRaw, fechaSoloRaw, horaSoloRaw) {
        var fechaSolo = (fechaSoloRaw || '').trim();
        var horaSolo = (horaSoloRaw || '').trim();
        var raw = String(fechaRaw || '').trim();

        if ((!fechaSolo || !horaSolo) && raw) {
            var partes = raw.split(/\s+/);
            if (!fechaSolo && partes[0]) fechaSolo = partes[0];
            if (!horaSolo && partes[1]) horaSolo = partes[1];
        }

        if (fechaSolo && fechaSolo.indexOf('/') !== -1) {
            var p = fechaSolo.split('/');
            if (p.length === 3) {
                fechaSolo = p[0].padStart(2, '0') + '/' + p[1].padStart(2, '0') + '/' + p[2];
            }
        }

        var horaFormateada = horaSolo || '08:00';
        if (horaFormateada && /^\d{1,2}:\d{2}$/.test(horaFormateada)) {
            var hm = horaFormateada.split(':');
            var hh = parseInt(hm[0], 10);
            var mm = hm[1];
            var ampm = hh >= 12 ? 'p.m.' : 'a.m.';
            var hh12 = hh % 12;
            if (hh12 === 0) hh12 = 12;
            horaFormateada = String(hh12).padStart(2, '0') + ':' + mm + ' ' + ampm;
        }

        return {
            fecha_solo: fechaSolo || 'Fecha reciente',
            hora_solo: horaFormateada
        };
    }

    function normNoticia(n, idx) {
        var pick = function (ks) {
            for (var i = 0; i < ks.length; i++) {
                if (n && n[ks[i]]) return n[ks[i]];
            }
            return '';
        };
        var tituloRaw = normalizarCVG(decodificarUnicode(pick(['titulo', 'title'])));
        var subtituloRaw = normalizarCVG(decodificarUnicode(pick(['subtitulo', 'categoria', 'category'])));
        var resumenRaw = normalizarCVG(decodificarUnicode(pick(['resumen', 'descripcion', 'extracto', 'subtitulo'])));
        var esCategoriaCorta = subtituloRaw && subtituloRaw.length <= 42;
        var categoria = normalizarCVG(pick(['categoria', 'category']) || (esCategoriaCorta ? subtituloRaw : 'Gestión Industrial'));
        var resumen = resumenRaw || tituloRaw;

        var fallbackImgs = [
            '/assets/img/noticias/noticia-761.jpg',
            '/assets/img/noticias/noticia-760.jpg',
            '/assets/img/noticias/noticia-759.jpg',
            '/assets/img/noticias/noticia-758.jpg',
            '/assets/img/noticias/noticia-757.jpg',
            '/assets/img/noticias/noticia-753.jpg',
            '/assets/img/noticias/noticia-752.jpg',
            '/assets/img/noticias/noticia-751.jpg',
            '/assets/img/noticias/noticia-750.jpg',
            '/assets/img/noticias/noticia-749.jpg',
            '/assets/img/noticias/noticia-748.jpg',
            '/assets/img/noticias/noticia-739.jpg'
        ];
        var defImg = fallbackImgs[idx % fallbackImgs.length];
        var img = pick(['imagen', 'portada', 'miniatura', 'imagen_url', 'thumbnail', 'foto']) || defImg;
        var rawImgs = (n && Array.isArray(n.imagenes)) ? n.imagenes : [];
        var imagenes = [];
        for (var j = 0; j < rawImgs.length && imagenes.length < 20; j++) {
            var it = rawImgs[j];
            if (typeof it === 'string' && it.trim()) {
                imagenes.push({ url: it.trim(), pie: '' });
            } else if (it && it.url && String(it.url).trim()) {
                imagenes.push({ url: String(it.url).trim(), pie: normalizarCVG(decodificarUnicode(it.pie || '')) });
            }
        }
        if (!imagenes.length) {
            imagenes.push({ url: img || defImg, pie: '' });
        }
        img = imagenes[0].url || img || defImg;

        var fh = separarFechaHora(pick(['fecha', 'created_at', 'date']), pick(['fecha_solo']), pick(['hora_solo']));
        var cuerpoRaw = normalizarCVG(decodificarUnicode(pick(['cuerpo', 'contenido']) || resumen));

        return {
            id: pick(['id', 'noticia_id']),
            orden: idx + 1,
            estado: pick(['estado']) || 'publicada',
            categoria: categoria,
            subtitulo: subtituloRaw,
            titulo: tituloRaw,
            resumen: resumen,
            cuerpo: cuerpoRaw,
            imagen: img,
            imagenes: imagenes,
            pie_foto: pick(['pie_foto']) || (imagenes[0] && imagenes[0].pie) || '',
            autor: pick(['autor']) || 'Gerencia de Comunicación Estratégica',
            fecha: pick(['fecha', 'created_at', 'date']),
            fecha_solo: fh.fecha_solo,
            hora_solo: fh.hora_solo,
            url: pick(['url', 'enlace', 'link']) || (C.contacto && C.contacto.periodico) || 'http://alcasa.com.ve:5001/minimal'
        };
    }

    var prensaAuthUser = null;

    function renderCardNoticia(n, idx) {
        var defFallback = '/assets/img/noticias/noticia-761.jpg';
        var imgs = (Array.isArray(n.imagenes) && n.imagenes.length) ? n.imagenes.slice(0, 20) : [{ url: n.imagen || defFallback, pie: '' }];
        var slidesHtml = imgs.map(function (im, sIdx) {
            var srcUrl = (im && im.url) ? im.url : (n.imagen || defFallback);
            return '<img ' + (sIdx === 0 ? 'loading="eager"' : 'loading="lazy"') + ' decoding="async" class="prensa-api-thumb prensa-carousel-slide' + (sIdx === 0 ? ' active' : '') + '" ' +
                'data-slide-idx="' + sIdx + '" src="' + esc(srcUrl) + '" alt="' + esc(im.pie || n.titulo) + '" ' +
                'style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border:none;outline:none;" ' +
                'onerror="this.onerror=null;this.src=\'' + defFallback + '\';">';
        }).join('');

        return '' +
            '<article class="prensa-api-card prensa-card-horizontal' + (idx === 0 ? ' is-top-news' : '') + '" data-noticia-idx="' + idx + '" data-curr-slide="0">' +
                '<div class="prensa-api-thumb-wrap prensa-thumb-carousel">' +
                    '<div class="prensa-carousel-stage" style="position:relative;width:100%;height:100%;overflow:hidden;">' +
                        slidesHtml +
                    '</div>' +
                '</div>' +
                '<div class="prensa-api-card-body">' +
                    '<div class="prensa-row-meta">' +
                        '<span class="prensa-cat-badge"><i class="fas fa-tag"></i> ' + esc(n.categoria) + '</span>' +
                        '<span class="prensa-strip-item" title="Fecha de publicación"><i class="fas fa-calendar-alt"></i> ' + esc(n.fecha_solo) + '</span>' +
                        '<span class="prensa-strip-item" title="Hora de publicación"><i class="fas fa-clock"></i> ' + esc(n.hora_solo) + '</span>' +
                    '</div>' +
                    '<h3 class="prensa-api-title" title="' + esc(n.titulo) + '">' + esc(n.titulo) + '</h3>' +
                    '<p class="prensa-api-excerpt">' + esc(n.resumen) + '</p>' +
                '</div>' +
                '<div class="prensa-api-footer">' +
                    '<button type="button" class="btn-prensa-quickview-sm" data-noticia-idx="' + idx + '" title="Ampliar comunicado y galería">' +
                        '<i class="fas fa-eye"></i> Vista rápida' +
                    '</button>' +
                    '<a href="' + esc(n.url) + '" target="_blank" rel="noopener noreferrer" class="btn-prensa-card">' +
                        'Leer nota <i class="fas fa-arrow-right"></i>' +
                    '</a>' +
                '</div>' +
            '</article>';
    }

    function initNoticias() {
        var gridEl = $('#prensaNoticiasGrid') || $('#noticias');
        if (!gridEl) return;

        var counterEl = $('#prensaApiCounter') || $('#newsCounter');
        var btnMasEl = $('#btnMostrarMasPrensa') || $('#mas');
        var iframeFallback = $('#prensaIframeFallback') || $('#iframeContainer');
        var iframeEl = $('#prensaIframeEl') || $('#iframeFallback');
        var detailPanel = $('#noticiaDetailPanel');
        var detailCloseBtn = $('#noticiaDetailCloseBtn') || $('#noticiaPanelCloseBtn');
        var detailFooterCloseBtn = $('#noticiaModalCloseFooterBtn');
        var paginationBar = $('#prensaPaginationBar');
        var isPaginatedMode = gridEl.getAttribute('data-paginated') === 'true';

        if (detailPanel && detailPanel.parentElement !== document.body) {
            document.body.appendChild(detailPanel);
        }

        var maxAttr = parseInt(gridEl.getAttribute('data-max') || '10', 10);
        var initialVisible = maxAttr || 10;
        var todas = [];
        var mostrandoTodas = false;
        var currentPage = 1;
        var totalPages = 72;

        try {
            var pParams = new URLSearchParams(window.location.search);
            var pQuery = parseInt(pParams.get('page') || '1', 10);
            if (pQuery > 0) currentPage = pQuery;
        } catch (e) {}

        function renderPaginacion(curr, total) {
            if (!paginationBar || !isPaginatedMode) return;
            if (total <= 1) {
                paginationBar.style.display = 'none';
                return;
            }
            paginationBar.style.display = 'flex';
            var html = '';
            html += '<button type="button" class="prensa-page-btn" data-page="1" ' + (curr <= 1 ? 'disabled' : '') + ' title="Primera página">«</button>';
            html += '<button type="button" class="prensa-page-btn" data-page="' + Math.max(1, curr - 1) + '" ' + (curr <= 1 ? 'disabled' : '') + ' title="Página anterior">‹</button>';

            var startP = Math.max(1, curr - 2);
            var endP = Math.min(total, startP + 4);
            if (endP - startP < 4) startP = Math.max(1, endP - 4);

            if (startP > 1) {
                html += '<button type="button" class="prensa-page-btn" data-page="1">1</button>';
                if (startP > 2) html += '<span class="prensa-page-ellipsis">…</span>';
            }
            for (var p = startP; p <= endP; p++) {
                html += '<button type="button" class="prensa-page-btn' + (p === curr ? ' active' : '') + '" data-page="' + p + '">' + p + '</button>';
            }
            if (endP < total) {
                if (endP < total - 1) html += '<span class="prensa-page-ellipsis">…</span>';
                html += '<button type="button" class="prensa-page-btn" data-page="' + total + '">' + total + '</button>';
            }

            html += '<button type="button" class="prensa-page-btn" data-page="' + Math.min(total, curr + 1) + '" ' + (curr >= total ? 'disabled' : '') + ' title="Página siguiente">›</button>';
            html += '<button type="button" class="prensa-page-btn" data-page="' + total + '" ' + (curr >= total ? 'disabled' : '') + ' title="Última página">»</button>';

            paginationBar.innerHTML = html;
            $$('.prensa-page-btn', paginationBar).forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (this.disabled) return;
                    var targetP = parseInt(this.getAttribute('data-page') || '1', 10);
                    if (targetP === currentPage) return;
                    currentPage = targetP;
                    try {
                        var u = new URL(window.location.href);
                        u.searchParams.set('page', String(currentPage));
                        window.history.pushState({}, '', u.toString());
                    } catch (e) {}
                    cargarListaNoticias(false);
                    var topBox = $('.page-header-box') || gridEl;
                    if (topBox) topBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });
        }

        var filterQInput = $('#txtBuscar');
        var filterFechaInput = $('#fechaBuscar');
        var filterMesSelect = $('#mesBuscar');
        var filterAnioSelect = $('#anioBuscar');
        var btnBuscarMinimal = $('#btnBuscarMinimal');
        var btnLimpiarMinimal = $('#btnLimpiarMinimal');
        var filtroInfoEl = $('#filtroInfo');

        function obtenerFiltrosActivos() {
            return {
                q: filterQInput ? filterQInput.value.trim() : '',
                fecha: filterFechaInput ? filterFechaInput.value.trim() : '',
                mes: filterMesSelect ? filterMesSelect.value.trim() : '',
                anio: filterAnioSelect ? filterAnioSelect.value.trim() : ''
            };
        }

        function actualizarBannerFiltros(f) {
            if (!filtroInfoEl) return;
            var partes = [];
            if (f.q) partes.push('🔍 "' + f.q + '"');
            if (f.fecha) partes.push('📅 Fecha: ' + f.fecha);
            if (f.mes) {
                var mesesNombres = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                partes.push('📆 Mes: ' + (mesesNombres[parseInt(f.mes, 10) - 1] || f.mes));
            }
            if (f.anio) partes.push('📅 Año: ' + f.anio);
            if (partes.length) {
                filtroInfoEl.textContent = '🔎 Filtros activos: ' + partes.join(' · ');
                filtroInfoEl.style.display = 'block';
            } else {
                filtroInfoEl.style.display = 'none';
            }
        }

        function cargarListaNoticias(forzarSinCache) {
            if (isPaginatedMode) {
                var fAct = obtenerFiltrosActivos();
                var hayFiltro = !!(fAct.q || fAct.fecha || fAct.mes || fAct.anio);
                actualizarBannerFiltros(fAct);

                if (counterEl) {
                    counterEl.innerHTML = '<i class="fas fa-spinner fa-spin" style="color: var(--alcasa-accent);"></i> <span>Cargando página ' + currentPage + ' de noticias oficiales de CVG Alcasa...</span>';
                }
                var qs = ['page=' + currentPage];
                if (fAct.q) qs.push('q=' + encodeURIComponent(fAct.q));
                if (fAct.fecha) qs.push('fecha=' + encodeURIComponent(fAct.fecha));
                if (fAct.mes) qs.push('mes=' + encodeURIComponent(fAct.mes));
                if (fAct.anio) qs.push('anio=' + encodeURIComponent(fAct.anio));
                if (forzarSinCache) qs.push('nocache=' + Date.now());

                var pageUrl = CFG.noticias + '?' + qs.join('&');
                return getJSON(pageUrl)
                    .catch(function () {
                        return getJSON(CFG.respaldo).then(function (arr) {
                            var perPage = 10;
                            var totP = Math.max(1, Math.ceil(arr.length / perPage));
                            return {
                                page: currentPage,
                                total_pages: totP,
                                noticias: arr.slice((currentPage - 1) * perPage, currentPage * perPage)
                            };
                        });
                    })
                    .then(function (resp) {
                        var arr = Array.isArray(resp) ? resp : (resp && Array.isArray(resp.noticias) ? resp.noticias : []);
                        if (resp && resp.total_pages) totalPages = parseInt(resp.total_pages, 10) || (hayFiltro ? 1 : 72);
                        todas = arr.map(normNoticia).filter(function (n) { return !!n.titulo; });
                        if (!todas.length && !hayFiltro) throw new Error('Sin noticias');
                        if (iframeFallback) iframeFallback.classList.remove('active');
                        dibujar();
                        renderPaginacion(currentPage, totalPages);
                    })
                    .catch(function () {
                        if (counterEl) {
                            counterEl.innerHTML = '<i class="fas fa-newspaper" style="color: var(--alcasa-accent);"></i> <span>Periódico Digital de CVG Alcasa en línea</span>';
                        }
                        gridEl.innerHTML = '';
                        if (iframeFallback && iframeEl) {
                            if (!iframeEl.getAttribute('src')) {
                                iframeEl.setAttribute('src', iframeEl.getAttribute('data-src') || '/minimal.html');
                            }
                            iframeFallback.classList.add('active');
                        }
                    });
            }

            var urlApi = CFG.noticias + (forzarSinCache ? '?nocache=' + Date.now() : '');
            return getJSON(urlApi)
                .catch(function () { return getJSON(CFG.respaldo); })
                .then(function (lista) {
                    var arr = Array.isArray(lista) ? lista : (lista && Array.isArray(lista.noticias) ? lista.noticias : []);
                    todas = arr.map(normNoticia).filter(function (n) { return !!n.titulo; });
                    if (!todas.length) throw new Error('Sin noticias');
                    if (iframeFallback) iframeFallback.classList.remove('active');
                    dibujar();
                })
                .catch(function () {
                    if (counterEl) {
                        counterEl.innerHTML = '<i class="fas fa-newspaper" style="color: var(--alcasa-accent);"></i> <span>Periódico Digital de CVG Alcasa en línea</span>';
                    }
                    gridEl.innerHTML = '';
                    if (iframeFallback && iframeEl) {
                        if (!iframeEl.getAttribute('src')) {
                            iframeEl.setAttribute('src', iframeEl.getAttribute('data-src') || '/minimal.html');
                        }
                        iframeFallback.classList.add('active');
                    }
                });
        }

        var detailSlideIdx = 0;
        var detailNoticiaIdx = 0;

        function renderDetailSlide() {
            var n = todas[detailNoticiaIdx];
            if (!n || !detailPanel) return;
            var imgs = (Array.isArray(n.imagenes) && n.imagenes.length) ? n.imagenes : [{ url: n.imagen || PH, pie: '' }];
            if (detailSlideIdx < 0) detailSlideIdx = imgs.length - 1;
            if (detailSlideIdx >= imgs.length) detailSlideIdx = 0;
            var actual = imgs[detailSlideIdx] || { url: n.imagen || PH, pie: '' };

            var imgEl = $('#noticiaDetailImg') || $('#noticiaPanelImg');
            if (imgEl) {
                imgEl.src = actual.url || PH;
                imgEl.alt = actual.pie || n.titulo;
                var wrap = imgEl.parentElement;
                if (wrap) {
                    wrap.style.position = 'relative';
                    var badgeCounter = $('.detail-carousel-badge', wrap);
                    if (!badgeCounter) {
                        badgeCounter = document.createElement('div');
                        badgeCounter.className = 'detail-carousel-badge';
                        wrap.appendChild(badgeCounter);
                    }
                    badgeCounter.innerHTML = '<i class="fas fa-images"></i> ' + (detailSlideIdx + 1) + ' / ' + imgs.length +
                        (actual.pie ? ' &nbsp;·&nbsp; <span>' + esc(actual.pie) + '</span>' : '');

                    var navWrap = $('.detail-carousel-controls', wrap);
                    if (imgs.length > 1) {
                        if (!navWrap) {
                            navWrap = document.createElement('div');
                            navWrap.className = 'detail-carousel-controls';
                            navWrap.innerHTML = '' +
                                '<button type="button" class="prensa-carousel-nav prev" data-det-dir="-1" aria-label="Anterior"><i class="fas fa-chevron-left"></i></button>' +
                                '<button type="button" class="prensa-carousel-nav next" data-det-dir="1" aria-label="Siguiente"><i class="fas fa-chevron-right"></i></button>';
                            wrap.appendChild(navWrap);
                            $$('button[data-det-dir]', navWrap).forEach(function (b) {
                                b.addEventListener('click', function (ev) {
                                    ev.preventDefault();
                                    ev.stopPropagation();
                                    detailSlideIdx += parseInt(this.getAttribute('data-det-dir') || '1', 10);
                                    renderDetailSlide();
                                });
                            });
                        }
                        navWrap.style.display = 'block';
                    } else if (navWrap) {
                        navWrap.style.display = 'none';
                    }
                }
            }
        }

        function cerrarDetalleNoticia() {
            if (!detailPanel) return;
            detailPanel.style.display = 'none';
            document.body.style.overflow = '';
        }

        function abrirDetalleNoticia(idx, startSlide) {
            var n = todas[idx];
            if (!n || !detailPanel) return;
            detailNoticiaIdx = idx;
            detailSlideIdx = typeof startSlide === 'number' ? startSlide : 0;

            var catEl = $('#noticiaDetailCat') || $('#noticiaPanelCategoria');
            var dtEl = $('#noticiaDetailDatetime') || $('#noticiaPanelMeta');
            var titleEl = $('#noticiaDetailTitle') || $('#noticiaPanelTitulo');
            var textEl = $('#noticiaDetailText') || $('#noticiaPanelResumen');
            var linkEl = $('#noticiaDetailExternalLink') || $('#noticiaPanelLink');

            if (catEl) catEl.innerHTML = '<i class="fas fa-bolt"></i> Noticia #' + String(idx + 1).padStart(2, '0') + ' · ' + esc(n.categoria);
            if (dtEl) dtEl.innerHTML = '<i class="fas fa-calendar-day"></i> ' + esc(n.fecha_solo) + ' &nbsp;|&nbsp; <i class="fas fa-clock"></i> ' + esc(n.hora_solo) + (n.autor ? ' &nbsp;|&nbsp; <i class="fas fa-user-pen"></i> ' + esc(n.autor) : '');
            if (titleEl) titleEl.textContent = n.titulo;
            if (textEl) textEl.textContent = n.cuerpo || n.resumen;
            if (linkEl) linkEl.href = n.url;

            renderDetailSlide();

            detailPanel.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        if (detailCloseBtn && detailPanel) {
            detailCloseBtn.addEventListener('click', cerrarDetalleNoticia);
        }
        if (detailFooterCloseBtn && detailPanel) {
            detailFooterCloseBtn.addEventListener('click', cerrarDetalleNoticia);
        }
        if (detailPanel) {
            detailPanel.addEventListener('click', function (e) {
                if (e.target === detailPanel) cerrarDetalleNoticia();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && detailPanel.style.display !== 'none') {
                    cerrarDetalleNoticia();
                }
            });
        }

        function cambiarSlideCard(card, nuevoIdx) {
            if (!card) return;
            var slides = $$('.prensa-carousel-slide', card);
            if (!slides.length) return;
            var total = slides.length;
            if (nuevoIdx < 0) nuevoIdx = total - 1;
            if (nuevoIdx >= total) nuevoIdx = 0;
            card.setAttribute('data-curr-slide', String(nuevoIdx));
            slides.forEach(function (sl, i) {
                sl.classList.toggle('active', i === nuevoIdx);
            });
            var currEl = $('.prensa-carousel-curr', card);
            if (currEl) currEl.textContent = String(nuevoIdx + 1);
            $$('.prensa-carousel-dot', card).forEach(function (d, i) {
                d.classList.toggle('active', i === nuevoIdx);
            });
        }

        var autoCarouselTimer = null;

        function iniciarAutoCarruselNoticias() {
            if (autoCarouselTimer) clearInterval(autoCarouselTimer);
            // Transición automática cada 6 segundos (dentro del rango solicitado de 5 a 10 segundos)
            autoCarouselTimer = setInterval(function () {
                $$('.prensa-api-card', gridEl).forEach(function (card) {
                    if (card.dataset.hovered === '1') return;
                    var slides = $$('.prensa-carousel-slide', card);
                    if (slides.length <= 1) return;
                    var curr = parseInt(card.getAttribute('data-curr-slide') || '0', 10);
                    cambiarSlideCard(card, curr + 1);
                });

                if (detailPanel && detailPanel.style.display !== 'none' && detailPanel.dataset.hovered !== '1') {
                    var nDet = todas[detailNoticiaIdx];
                    if (nDet && Array.isArray(nDet.imagenes) && nDet.imagenes.length > 1) {
                        detailSlideIdx++;
                        renderDetailSlide();
                    }
                }
            }, 6000);
        }

        if (detailPanel && detailPanel.dataset.hoverBound !== '1') {
            detailPanel.dataset.hoverBound = '1';
            detailPanel.addEventListener('mouseenter', function () { detailPanel.dataset.hovered = '1'; });
            detailPanel.addEventListener('mouseleave', function () { detailPanel.dataset.hovered = '0'; });
        }

        function bindNoticiaEvents() {
            $$('.prensa-api-card', gridEl).forEach(function (card) {
                if (card.dataset.hoverBound === '1') return;
                card.dataset.hoverBound = '1';
                card.addEventListener('mouseenter', function () { card.dataset.hovered = '1'; });
                card.addEventListener('mouseleave', function () { card.dataset.hovered = '0'; });
            });

            iniciarAutoCarruselNoticias();

            $$('.prensa-carousel-nav', gridEl).forEach(function (btn) {
                if (btn.dataset.boundNav === '1') return;
                btn.dataset.boundNav = '1';
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var card = this.closest('[data-noticia-idx]');
                    if (!card) return;
                    var curr = parseInt(card.getAttribute('data-curr-slide') || '0', 10);
                    var dir = parseInt(this.getAttribute('data-dir') || '1', 10);
                    cambiarSlideCard(card, curr + dir);
                });
            });

            $$('.prensa-carousel-dot', gridEl).forEach(function (dot) {
                if (dot.dataset.boundDot === '1') return;
                dot.dataset.boundDot = '1';
                dot.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var card = this.closest('[data-noticia-idx]');
                    if (!card) return;
                    var target = parseInt(this.getAttribute('data-dot-idx') || '0', 10);
                    cambiarSlideCard(card, target);
                });
            });

            $$('.btn-prensa-quickview, .btn-prensa-quickview-sm, .prensa-carousel-stage, .prensa-api-title', gridEl).forEach(function (el) {
                if (el.dataset.boundQuick === '1') return;
                el.dataset.boundQuick = '1';
                el.style.cursor = 'pointer';
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    var card = this.closest('[data-noticia-idx]');
                    var idx = parseInt((card && card.getAttribute('data-noticia-idx')) || this.getAttribute('data-noticia-idx') || '0', 10);
                    var currSlide = card ? parseInt(card.getAttribute('data-curr-slide') || '0', 10) : 0;
                    abrirDetalleNoticia(idx, currSlide);
                });
            });

            $$('.btn-prensa-del-sm', gridEl).forEach(function (btn) {
                if (btn.dataset.boundDel === '1') return;
                btn.dataset.boundDel = '1';
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var idx = parseInt(this.getAttribute('data-noticia-idx') || '0', 10);
                    var n = todas[idx];
                    if (!n || !confirm('¿Eliminar la noticia "' + n.titulo + '" de la Sala de Prensa?')) return;
                    var fd = new FormData();
                    fd.append('action', 'delete');
                    fd.append('id', n.id || String(idx + 1));
                    fetch('/api/prensa_admin.php?action=delete', { method: 'POST', body: fd })
                        .then(function (r) { return r.json(); })
                        .then(function (res) {
                            if (res && res.success) {
                                cargarListaNoticias(true);
                            } else {
                                alert((res && res.error) || 'No se pudo eliminar la noticia.');
                            }
                        })
                        .catch(function () { alert('Error de conexión con el servidor PHP.'); });
                });
            });
        }

        function dibujar() {
            var limite = isPaginatedMode ? todas.length : (mostrandoTodas ? todas.length : initialVisible);
            var visibles = todas.slice(0, limite);

            if (counterEl) {
                if (isPaginatedMode) {
                    counterEl.innerHTML = '<i class="fas fa-bolt" style="color: var(--alcasa-accent);"></i> ' +
                        '<span>Página <strong>' + currentPage + '</strong> de <strong>' + totalPages + '</strong> · Mostrando <strong>' + visibles.length + ' noticias oficiales</strong> de <strong>CVG Aluminio del Caroní, S.A. (CVG Alcasa)</strong></span>';
                } else {
                    counterEl.innerHTML = '<i class="fas fa-bolt" style="color: var(--alcasa-accent);"></i> ' +
                        '<span>Mostrando <strong>' + visibles.length + '</strong> de <strong>' + todas.length + ' noticias publicadas</strong> en <strong>CVG Aluminio del Caroní, S.A. (CVG Alcasa)</strong></span>';
                }
            }

            if (!visibles.length) {
                gridEl.innerHTML = '<p>No hay noticias disponibles por ahora.</p>';
                return;
            }

            // Lista horizontal apilada (una debajo de la otra, tamaño equilibrado y legible)
            gridEl.innerHTML = '<div class="prensa-list-horizontal">' +
                visibles.map(function (n, i) {
                    return renderCardNoticia(n, i);
                }).join('') +
            '</div>';
            bindNoticiaEvents();

            if (btnMasEl) {
                if (!isPaginatedMode && todas.length > initialVisible) {
                    btnMasEl.style.display = 'inline-flex';
                    btnMasEl.hidden = false;
                    btnMasEl.innerHTML = mostrandoTodas
                        ? '<i class="fas fa-compress-alt"></i> Mostrar solo el Top 10 de noticias'
                        : '<i class="fas fa-plus-circle"></i> Ver archivo adicional (' + (todas.length - initialVisible) + ' noticias más)';
                } else {
                    btnMasEl.style.display = 'none';
                    btnMasEl.hidden = true;
                }
            }
        }

        if (btnMasEl) {
            btnMasEl.addEventListener('click', function () {
                mostrandoTodas = !mostrandoTodas;
                dibujar();
            });
        }

        if (btnBuscarMinimal) {
            btnBuscarMinimal.addEventListener('click', function (e) {
                e.preventDefault();
                currentPage = 1;
                cargarListaNoticias(false);
            });
        }
        if (filterQInput) {
            filterQInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    currentPage = 1;
                    cargarListaNoticias(false);
                }
            });
        }
        if (btnLimpiarMinimal) {
            btnLimpiarMinimal.addEventListener('click', function (e) {
                e.preventDefault();
                if (filterQInput) filterQInput.value = '';
                if (filterFechaInput) filterFechaInput.value = '';
                if (filterMesSelect) filterMesSelect.value = '';
                if (filterAnioSelect) filterAnioSelect.value = '';
                currentPage = 1;
                cargarListaNoticias(false);
            });
        }

        cargarListaNoticias(false);
    }

    /* ======================================================================
       7. FORMULARIOS BACKEND PHP (CONTACTO Y POSTULACIÓN DE CV)
       ====================================================================== */
    function initFormularios() {
        // 7.1 Formulario de Contacto (/api/contacto.php)
        var formContacto = $('#formContacto') || $('#contacto-form');
        if (formContacto) {
            var btnContacto = $('#btnContactoEnviar') || $('button[type="submit"]', formContacto);
            var msgContacto = $('#contactoMensaje') || $('#msg');

            formContacto.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!formContacto.nombre.value.trim() || !formContacto.correo.value.trim()) {
                    if (msgContacto) {
                        msgContacto.textContent = 'Por favor completa los campos obligatorios (*).';
                        msgContacto.className = 'contacto-mensaje error';
                    }
                    return;
                }

                if (btnContacto) {
                    btnContacto.disabled = true;
                    btnContacto.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
                }
                if (msgContacto) {
                    msgContacto.textContent = '';
                    msgContacto.className = 'contacto-mensaje';
                }

                fetch(CFG.contacto, {
                    method: 'POST',
                    body: new FormData(formContacto)
                })
                .then(function (r) {
                    return r.json().then(function (j) {
                        return r.ok && j && j.success ? j : Promise.reject(j || { error: 'HTTP ' + r.status });
                    });
                })
                .then(function (res) {
                    if (msgContacto) {
                        msgContacto.textContent = res.mensaje || '¡Gracias! Hemos recibido tu mensaje. Nos pondremos en contacto contigo pronto.';
                        msgContacto.className = 'contacto-mensaje exito';
                    }
                    formContacto.reset();
                })
                .catch(function (err) {
                    if (msgContacto) {
                        msgContacto.textContent = (err && err.error) || 'No se pudo enviar el mensaje.Verifica que el servidor PHP esté activo.';
                        msgContacto.className = 'contacto-mensaje error';
                    }
                })
                .finally(function () {
                    if (btnContacto) {
                        btnContacto.disabled = false;
                        btnContacto.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Mensaje';
                    }
                });
            });
        }

        // 7.2 Formulario de Postulación de Síntesis Curricular (/api/cv.php)
        var formCV = $('#formPostulacionCV') || $('#cv-form');
        if (formCV) {
            var fileInput = $('#cv-archivo', formCV);
            var dropzone = $('#cvDropzone');
            var dropzoneIcon = $('#cvDropzoneIcon');
            var dropzoneTitle = $('#cvDropzoneTitle');
            var dropzoneHint = $('#cvDropzoneHint');
            var btnCV = $('#btnEnviarCV') || $('button[type="submit"]', formCV);
            var msgCV = $('#cvMensaje') || $('#cv-msg');

            function resetDropzone() {
                if (dropzone) dropzone.classList.remove('has-file');
                if (dropzoneIcon) dropzoneIcon.className = 'fas fa-cloud-arrow-up';
                if (dropzoneTitle) dropzoneTitle.textContent = 'Haz clic o arrastra aquí tu archivo de CV';
                if (dropzoneHint) dropzoneHint.textContent = 'Formatos admitidos: PDF, DOC o DOCX (Máx. 10 MB)';
            }

            if (fileInput) {
                fileInput.addEventListener('change', function () {
                    var file = this.files && this.files[0];
                    if (file) {
                        if (file.size > 10 * 1024 * 1024) {
                            alert('El archivo supera el límite máximo permitido de 10 MB.');
                            this.value = '';
                            resetDropzone();
                            return;
                        }
                        var sizeLabel = file.size > 1048576
                            ? (file.size / 1048576).toFixed(2) + ' MB'
                            : (file.size / 1024).toFixed(1) + ' KB';
                        if (dropzone) dropzone.classList.add('has-file');
                        if (dropzoneIcon) dropzoneIcon.className = 'fas fa-file-circle-check';
                        if (dropzoneTitle) dropzoneTitle.textContent = 'Archivo seleccionado: ' + file.name;
                        if (dropzoneHint) dropzoneHint.textContent = 'Tamaño: ' + sizeLabel + ' — Listo para adjuntar';
                    } else {
                        resetDropzone();
                    }
                });
            }

            formCV.addEventListener('submit', function (e) {
                e.preventDefault();
                var archivo = fileInput && fileInput.files ? fileInput.files[0] : null;
                if (!archivo) {
                    if (msgCV) {
                        msgCV.textContent = 'Por favor adjunta tu síntesis curricular (PDF, DOC o DOCX).';
                        msgCV.className = 'contacto-mensaje error';
                    }
                    return;
                }
                if (archivo.size > 10 * 1024 * 1024) {
                    if (msgCV) {
                        msgCV.textContent = 'El archivo de CV supera el límite máximo de 10 MB.';
                        msgCV.className = 'contacto-mensaje error';
                    }
                    return;
                }

                if (btnCV) {
                    btnCV.disabled = true;
                    btnCV.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando Postulación...';
                }
                if (msgCV) {
                    msgCV.textContent = '';
                    msgCV.className = 'contacto-mensaje';
                }

                var fd = new FormData(formCV);
                var nombreVal = (formCV.nombre && formCV.nombre.value || '').trim();
                var cedulaVal = (formCV.cedula && formCV.cedula.value || '').trim();
                var telefonoVal = (formCV.telefono && formCV.telefono.value || '').trim();
                var nivelVal = (formCV.nivel && formCV.nivel.value || '').trim();
                var areaVal = (formCV.area && formCV.area.value || '').trim();
                var resumenVal = (formCV.resumen && formCV.resumen.value || '').trim();

                fd.set('empresa', 'Postulación CV — ' + nivelVal);
                fd.set('interes', 'Oportunidad Laboral: ' + areaVal);
                fd.set('comentario', 'Teléfono: ' + telefonoVal + ' | Archivo CV adjunto: ' + archivo.name + ' (' + (archivo.size / 1024).toFixed(1) + ' KB) | Resumen: ' + (resumenVal || 'Sin resumen adicional'));

                fetch(CFG.cv, {
                    method: 'POST',
                    body: fd
                })
                .then(function (r) {
                    return r.json().then(function (j) {
                        return r.ok && j && j.success ? j : Promise.reject(j || { error: 'HTTP ' + r.status });
                    });
                })
                .then(function (res) {
                    if (msgCV) {
                        msgCV.textContent = res.mensaje || ('¡Postulación recibida con éxito! Tu Síntesis Curricular (' + archivo.name + ') ha sido registrada.');
                        msgCV.className = 'contacto-mensaje exito';
                    }
                    formCV.reset();
                    resetDropzone();
                })
                .catch(function (err) {
                    if (msgCV) {
                        msgCV.textContent = (err && err.error) || 'No se pudo enviar la postulación. Verifica que el servidor PHP esté activo.';
                        msgCV.className = 'contacto-mensaje error';
                    }
                })
                .finally(function () {
                    if (btnCV) {
                        btnCV.disabled = false;
                        btnCV.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Postulación y CV';
                    }
                });
            });
        }
    }

    /* ======================================================================
       8. INTERSECTION OBSERVER PARA SECCIONES (.fold-reveal) E INICIO GLOBAL
       ====================================================================== */
    function initFoldReveal() {
        var foldElements = $$('.fold-reveal');
        if ('IntersectionObserver' in window && foldElements.length > 0) {
            var foldObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        if (window.BlurText) {
                            $$('.blur-text', entry.target).forEach(function (bt) {
                                if (!bt.hasAttribute('data-blur-text-completed')) {
                                    window.BlurText.animate(bt);
                                }
                            });
                        }
                        observer.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '0px 0px -6% 0px', threshold: 0.1 });
            foldElements.forEach(function (el) { foldObserver.observe(el); });
        } else {
            foldElements.forEach(function (el) { el.classList.add('revealed'); });
        }
    }

    function boot() {
        renderHeader();
        renderFooter();
        getJSON(CFG.contenido)
            .then(function (d) { C = d || {}; })
            .catch(function () { C = {}; })
            .then(function () {
                renderHeader();
                renderFooter();
                BlurText.init();
                initHeroSlider();
                initEmpresaTabs();
                initProductosCatalogo();
                initFAQ();
                initGaleriaVideos();
                initPaginaGaleria();
                initNoticias();
                initFormularios();
                initFoldReveal();
                bindSmoothScrollLinks(document);

                var pl = $('#periodico');
                if (pl && C.contacto && C.contacto.periodico) {
                    pl.href = C.contacto.periodico;
                }
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
