/**
 * BlurText (Simplificado) - Animación limpia y sutil de entrada de texto (Fade & Slide suave)
 * Mantiene la misma API pública (BlurText, BlurText.animate, BlurText.init) sin fragmentar el DOM
 * en spans por palabra ni aplicar filtros blur pesados.
 */

(function (global, factory) {
    if (typeof exports === 'object' && typeof module !== 'undefined') {
        module.exports = factory();
    } else if (typeof define === 'function' && define.amd) {
        define(factory);
    } else {
        const exportsObj = factory();
        global.BlurText = exportsObj.BlurText || exportsObj;
        if (typeof window !== 'undefined') {
            window.BlurText = global.BlurText;
        }
    }
}(typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : this, function () {
    'use strict';

    function injectStyles() {
        if (typeof document === 'undefined') return;
        if (document.getElementById('blur-text-dynamic-styles')) return;

        const style = document.createElement('style');
        style.id = 'blur-text-dynamic-styles';
        style.textContent = `
            .simple-text-reveal {
                opacity: 0;
                transform: translateY(12px);
                transition: opacity 0.45s ease-out, transform 0.45s ease-out;
                will-change: opacity, transform;
            }
            .simple-text-reveal.simple-text-visible {
                opacity: 1 !important;
                transform: translateY(0) !important;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Aplica una transición sencilla de aparición (fade-up suave) al bloque de texto completo
     */
    function animateElement(element, options) {
        if (!element) return;
        injectStyles();

        const opts = Object.assign({
            onAnimationComplete: null,
            className: '',
            threshold: 0.1,
            rootMargin: '0px 0px -20px 0px',
            immediate: false
        }, options || {});

        if (opts.className) {
            opts.className.split(' ').filter(Boolean).forEach(c => element.classList.add(c));
        }

        // Reiniciar suavemente si se invoca de forma inmediata (ej. cambio de slide o tab)
        if (opts.immediate) {
            element.classList.remove('simple-text-visible');
            element.classList.add('simple-text-reveal');
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    element.classList.add('simple-text-visible');
                    element.setAttribute('data-blur-text-completed', 'true');
                    if (typeof opts.onAnimationComplete === 'function') {
                        setTimeout(opts.onAnimationComplete, 450);
                    }
                });
            });
            return {
                trigger: () => element.classList.add('simple-text-visible'),
                elements: [element]
            };
        }

        element.classList.add('simple-text-reveal');

        let animated = false;
        function triggerAnimation() {
            if (animated) return;
            animated = true;

            requestAnimationFrame(() => {
                element.classList.add('simple-text-visible');
                setTimeout(() => {
                    if (typeof opts.onAnimationComplete === 'function') {
                        opts.onAnimationComplete();
                    }
                    element.setAttribute('data-blur-text-completed', 'true');
                }, 450);
            });
        }

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        triggerAnimation();
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: opts.threshold,
                rootMargin: opts.rootMargin
            });
            observer.observe(element);
        } else {
            setTimeout(triggerAnimation, 50);
        }

        return {
            trigger: triggerAnimation,
            elements: [element]
        };
    }

    function BlurText(props) {
        if (props && props.element) {
            return animateElement(props.element, props);
        }

        if (typeof document !== 'undefined') {
            const targets = document.querySelectorAll('[data-blur-text], .blur-text');
            targets.forEach(el => {
                if (el.getAttribute('data-blur-text-initialized')) return;
                el.setAttribute('data-blur-text-initialized', 'true');

                const immediate = el.hasAttribute('data-immediate') || false;

                animateElement(el, {
                    immediate,
                    onAnimationComplete: props && props.onAnimationComplete
                });
            });
        }

        return BlurText;
    }

    BlurText.animate = animateElement;
    BlurText.init = function (selector, options) {
        if (typeof document === 'undefined') return;
        const els = document.querySelectorAll(selector || '[data-blur-text], .blur-text');
        els.forEach(el => animateElement(el, options));
    };

    if (typeof document !== 'undefined') {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => BlurText());
        } else {
            BlurText();
        }
    }

    return BlurText;
}));
