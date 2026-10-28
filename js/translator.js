/* =========================================
   TRADUCTOR GLOBAL ALCASA
   Sistema de traduccion multi-idioma persistente
   ========================================= */
(function () {
    'use strict';

    var DEFAULT_LANG = 'es';
    var SUPPORTED = ['es', 'en', 'pt', 'fr', 'de', 'it', 'ar', 'zh-CN', 'ja'];
    var COOKIE_NAME = 'googtrans';
    var COOKIE_DAYS = 365;

    /* ---------- COOKIES ---------- */
    function getCookie(name) {
        var match = document.cookie.match(
            new RegExp('(^|;\\s*)' + name + '=([^;]*)')
        );
        return match ? decodeURIComponent(match[2]) : null;
    }

    function getCurrentLang() {
        var c = getCookie(COOKIE_NAME);
        if (!c) return DEFAULT_LANG;
        var parts = c.split('/');
        var lang = parts[parts.length - 1];
        return SUPPORTED.indexOf(lang) !== -1 ? lang : DEFAULT_LANG;
    }

    function setLangCookie(lang) {
        var value = '/' + DEFAULT_LANG + '/' + lang;
        var expires = new Date(Date.now() + COOKIE_DAYS * 864e5).toUTCString();

        document.cookie = COOKIE_NAME + '=' + value + ';path=/;expires=' + expires;
        document.cookie = COOKIE_NAME + '=' + value + ';path=/;domain=' + location.hostname + ';expires=' + expires;

        var parts = location.hostname.split('.');
        if (parts.length > 2) {
            var parent = '.' + parts.slice(-2).join('.');
            document.cookie = COOKIE_NAME + '=' + value + ';path=/;domain=' + parent + ';expires=' + expires;
        }
    }

    function clearLangCookie() {
        var past = 'Thu, 01 Jan 1970 00:00:00 GMT';
        document.cookie = COOKIE_NAME + '=;path=/;expires=' + past;
        document.cookie = COOKIE_NAME + '=;path=/;domain=' + location.hostname + ';expires=' + past;
        var parts = location.hostname.split('.');
        if (parts.length > 2) {
            var parent = '.' + parts.slice(-2).join('.');
            document.cookie = COOKIE_NAME + '=;path=/;domain=' + parent + ';expires=' + past;
        }
    }

    /* ---------- APLICAR IDIOMA EN VIVO ---------- */
    function aplicarIdiomaEnVivo(lang) {
        // Caso especial: volver al idioma original (español)
        // Google Translate NO puede volver al original solo cambiando el combo,
        // necesita que se borre la cookie y se recargue.
        if (lang === DEFAULT_LANG) {
            clearLangCookie();
            console.log('[Traductor] Volviendo al idioma original. Recargando...');
            window.location.reload();
            return;
        }

        // Buscar el combo interno de Google Translate
        var combo = document.querySelector('.goog-te-combo');

        if (!combo) {
            var intentos = (aplicarIdiomaEnVivo.intentos || 0) + 1;
            aplicarIdiomaEnVivo.intentos = intentos;

            if (intentos > 25) {
                console.warn('[Traductor] Combo no encontrado. Recargando...');
                aplicarIdiomaEnVivo.intentos = 0;
                window.location.reload();
                return;
            }
            setTimeout(function () { aplicarIdiomaEnVivo(lang); }, 200);
            return;
        }

        aplicarIdiomaEnVivo.intentos = 0;

        // Si el combo ya esta en ese idioma, no hacer nada
        if (combo.value === lang) {
            console.log('[Traductor] El idioma ya esta aplicado:', lang);
            return;
        }

        // Cambiar el combo y disparar el evento
        combo.value = lang;
        combo.dispatchEvent(new Event('change'));
        console.log('[Traductor] Idioma aplicado en vivo:', lang);
    }

    /* ---------- API PUBLICA ---------- */
    window.alcosaSetLanguage = function (lang) {
        if (SUPPORTED.indexOf(lang) === -1) return;

        // Guardar cookie
        if (lang === DEFAULT_LANG) {
            clearLangCookie();
        } else {
            setLangCookie(lang);
        }

        // Aplicar
        aplicarIdiomaEnVivo(lang);
    };

    /* ---------- GOOGLE TRANSLATE ---------- */
    window.googleTranslateElementInit = function () {
        if (!window.google || !window.google.translate) return;

        ensureGoogleContainer();

        new google.translate.TranslateElement(
            {
                pageLanguage: DEFAULT_LANG,
                includedLanguages: SUPPORTED.join(','),
                autoDisplay: false
            },
            'google_translate_element'
        );

        console.log('[Traductor] Google Translate inicializado.');
    };

    function ensureGoogleContainer() {
        if (document.getElementById('google_translate_element')) return;
        var div = document.createElement('div');
        div.id = 'google_translate_element';
        div.style.position = 'absolute';
        div.style.left = '-9999px';
        div.style.top = '-9999px';
        document.body.appendChild(div);
    }

    function loadGoogleTranslate() {
        if (document.querySelector('script[src*="translate_a/element.js"]')) return;
        var s = document.createElement('script');
        s.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
        s.async = true;
        document.head.appendChild(s);
    }

    /* ---------- ESTILOS ---------- */
    function injectStyles() {
        var style = document.createElement('style');
        style.textContent =
            '.goog-te-banner-frame,' +
            '.skiptranslate iframe,' +
            '#goog-gt-tt,' +
            '.goog-tooltip,' +
            '.goog-tooltip:hover { display: none !important; visibility: hidden !important; }' +
            'body { top: 0 !important; position: static !important; }' +
            '.goog-text-highlight { background: transparent !important; box-shadow: none !important; }' +
            '#google_translate_element { position: absolute !important; left: -9999px !important; }' +
            '.goog-logo-link, .goog-te-gadget span { display: none !important; }';
        document.head.appendChild(style);
    }

    /* ---------- INICIALIZACION ---------- */
    function init() {
        injectStyles();
        ensureGoogleContainer();
        loadGoogleTranslate();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();