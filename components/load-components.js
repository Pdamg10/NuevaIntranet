// components/load-components.js

function loadComponent(id, url, callback) {
    const contenedor = document.getElementById(id);
    if (!contenedor) return;

    fetch(url, { cache: 'no-store' })
        .then(response => response.text())
        .then(data => {
            contenedor.innerHTML = data;

            // Ejecutar scripts internos del componente
            contenedor.querySelectorAll("script").forEach(function (scriptViejo) {
                var scriptNuevo = document.createElement("script");
                if (scriptViejo.src) {
                    scriptNuevo.src = scriptViejo.src;
                } else {
                    scriptNuevo.textContent = scriptViejo.textContent;
                }
                scriptViejo.parentNode.replaceChild(scriptNuevo, scriptViejo);
            });

            if (callback) callback();
        })
        .catch(error => console.warn('Error cargando componente:', error));
}

// Cargar Header
loadComponent('header-container', '/components/header.html?v=' + Date.now());

// Cargar Sidebar y marcar el enlace activo
loadComponent('sidebar-container', '/components/sidebar.html', function() {
    const currentPath = window.location.pathname;
    document.querySelectorAll('#sidebar-container a').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href !== '#' && href !== '') {
            if (href.startsWith('/') && currentPath.endsWith(href)) {
                link.classList.add('active');
            }
        }
    });
});
