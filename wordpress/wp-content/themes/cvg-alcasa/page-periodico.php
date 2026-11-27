<?php
/**
 * Template Name: Periódico Digital
 *
 * Plantilla editorial autónoma de consulta pública del Periódico Digital CVG Alcasa.
 *
 * @package CVG_Alcasa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$inicio_url    = cvg_alcasa_page_url( 'inicio' );
$periodico_url = cvg_alcasa_page_url( 'periodico' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'admin-standalone-body' ); ?>>
<?php wp_body_open(); ?>

<div id="vistaPd">
	<div class="pd-top">
		<div class="pd-container">
			<span>📢 CVG Alcasa - Periódico Digital</span><span class="pd-top-sep">|</span>
			<span>📅 <span id="fechaActual">—</span></span><span class="pd-top-sep">|</span>
			<span>📍 Puerto Ordaz, Venezuela</span>
		</div>
	</div>

	<div class="pd-cabecera">
		<div class="pd-container pd-cabecera-in">
			<div class="pd-logo-area">
				<img src="<?php echo esc_url( cvg_alcasa_media_url( 'img/logo-alcasa.png' ) ); ?>" alt="Logo CVG Alcasa" />
				<div class="pd-titulo-peri">
					<h1>Periódico Digital</h1>
					<small>Información institucional</small>
				</div>
			</div>
			<div class="pd-fecha-edicion">
				<div class="pd-edicion-b">Edición N° 001</div>
				<div id="fechaEdicion">—</div>
			</div>
		</div>
	</div>

	<nav class="pd-navbar">
		<div class="pd-container">
			<ul class="pd-nav-links">
				<li><a href="<?php echo esc_url( $periodico_url ); ?>" id="navInicio"><i class="fas fa-house"></i> Inicio</a></li>
				<li><a href="<?php echo esc_url( $inicio_url ); ?>"><i class="fas fa-globe"></i> Portal</a></li>
			</ul>
		</div>
	</nav>

	<main class="pd-container pd-main">
		<div class="pd-flash" id="flashBox" style="display:none;"></div>

		<section id="vistaLista">
			<div class="pd-filters">
				<div class="pd-search">
					<i class="fas fa-magnifying-glass"></i>
					<input type="search" id="txtBuscar" placeholder="Buscar noticias..." />
				</div>
				<div class="pd-date">
					<i class="fas fa-calendar"></i>
					<input type="date" id="fecha" value="" />
				</div>
				<select id="mes" class="pd-sel">
					<option value="">-- Mes --</option>
					<option value="01">Enero</option><option value="02">Febrero</option><option value="03">Marzo</option>
					<option value="04">Abril</option><option value="05">Mayo</option><option value="06">Junio</option>
					<option value="07">Julio</option><option value="08">Agosto</option><option value="09">Septiembre</option>
					<option value="10">Octubre</option><option value="11">Noviembre</option><option value="12">Diciembre</option>
				</select>
				<select id="anio" class="pd-sel pd-sel-anio">
					<option value="">-- Año --</option>
					<option value="2026">2026</option>
					<option value="2025">2025</option>
				</select>
				<button type="button" class="pd-btn-buscar" id="btnBuscar"><i class="fas fa-magnifying-glass"></i> Buscar</button>
				<button type="button" class="pd-btn-limpiar" id="btnLimpiar"><i class="fas fa-eraser"></i> Limpiar</button>
			</div>

			<div class="paginacion" id="paginacionTop"><span class="disabled">‹</span><span class="active">1</span></div>

			<h1 class="pd-titulo-seccion"><i class="fas fa-bookmark"></i> Últimas Noticias</h1>

			<div class="pd-grid" id="noticiasGrid"></div>

			<div class="paginacion" id="paginacionBottom"><span class="disabled">‹</span><span class="active">1</span></div>
		</section>

		<section id="vistaNoticia" style="display:none;"></section>
	</main>
</div>

<div id="modal-aviso" class="pd-modal">
	<div class="pd-modal-box">
		<div class="pd-modal-head">
			<h3><i class="fas fa-scale-balanced"></i> Aviso Legal</h3>
			<button type="button" class="pd-modal-x" data-close-modal="aviso" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
		</div>
		<div class="pd-modal-body">
			<p><strong>CVG Alcasa</strong>, a través de su <strong>Gerencia de Comunicación Estratégica</strong>, pone a disposición de los usuarios este sitio web con el objetivo de informar sobre las actividades institucionales.</p>
			<p>El acceso y uso de este portal implican la aceptación plena de las siguientes condiciones:</p>
			<ul>
				<li><strong>Propiedad intelectual:</strong> Todos los contenidos (textos, imágenes, logotipos, diseños) son propiedad de CVG Alcasa o de terceros que han autorizado su uso. Queda prohibida su reproducción, distribución o modificación sin autorización expresa.</li>
				<li><strong>Responsabilidad:</strong> La información publicada es de carácter informativo. CVG Alcasa no se hace responsable del uso indebido que los usuarios puedan dar a la misma, ni de posibles errores u omisiones.</li>
				<li><strong>Enlaces externos:</strong> Este sitio puede contener enlaces a páginas externas sobre cuya gestión y contenidos CVG Alcasa no asume responsabilidad alguna.</li>
				<li><strong>Legislación aplicable:</strong> Las relaciones derivadas del uso de este portal se rigen por la legislación venezolana.</li>
			</ul>
			<p>Para cualquier consulta, puede dirigirse a la <strong>Gerencia de Comunicación Estratégica</strong> a través de los datos de contacto disponibles en este sitio.</p>
		</div>
	</div>
</div>

<div id="modal-privacidad" class="pd-modal">
	<div class="pd-modal-box">
		<div class="pd-modal-head">
			<h3><i class="fas fa-shield-halved"></i> Política de Privacidad</h3>
			<button type="button" class="pd-modal-x" data-close-modal="privacidad" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
		</div>
		<div class="pd-modal-body">
			<p>En CVG Alcasa nos comprometemos a proteger la privacidad de los visitantes de nuestro sitio web. Esta política explica cómo tratamos la información personal que pudiera recopilarse.</p>
			<ul>
				<li><strong>Recogida de datos:</strong> No se recopilan datos personales a menos que el usuario los proporcione voluntariamente (por ejemplo, al enviar un formulario de contacto).</li>
				<li><strong>Uso de la información:</strong> Los datos proporcionados se utilizarán exclusivamente para atender consultas, gestionar suscripciones o enviar información institucional, siempre con el consentimiento del usuario.</li>
				<li><strong>Cookies:</strong> Este sitio utiliza cookies técnicas para mejorar la experiencia de navegación. No almacenamos cookies de seguimiento o publicitarias sin su autorización.</li>
				<li><strong>Derechos de los usuarios:</strong> Puede solicitar la rectificación o eliminación de sus datos personales en cualquier momento, escribiendo a la Gerencia de Comunicación Estratégica.</li>
				<li><strong>Seguridad:</strong> Aplicamos medidas técnicas y organizativas adecuadas para proteger sus datos contra accesos no autorizados.</li>
			</ul>
			<p>Si tiene dudas sobre nuestra política de privacidad, no dude en contactarnos.</p>
		</div>
	</div>
</div>

<div id="modal-contacto" class="pd-modal">
	<div class="pd-modal-box">
		<div class="pd-modal-head">
			<h3><i class="fas fa-envelope"></i> Contacto</h3>
			<button type="button" class="pd-modal-x" data-close-modal="contacto" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
		</div>
		<div class="pd-modal-body">
			<p><strong>Gerencia de Comunicación Estratégica</strong><br />CVG Alcasa</p>
			<p><strong>Dirección:</strong><br />Zona Industrial Matanzas, Puerto Ordaz, Estado Bolívar, Venezuela.</p>
			<p><strong>Teléfonos:</strong><br />Extensiones: 1394 (Gerencia de Sistemas y Organización)<br />1028 (Gerencia de Comunicación Estratégica)</p>
			<p><strong>Correo electrónico:</strong><br /><a href="mailto:sebastian.marcano@alcasa.com.ve">sebastian.marcano@alcasa.com.ve</a></p>
			<p><strong>Horario de atención:</strong><br />Lunes a viernes de 7:00 a.m. a 3:00 p.m.</p>
			<p>Estamos a su disposición para cualquier consulta o sugerencia.</p>
		</div>
		<div class="pd-modal-foot">
			<button type="button" class="pd-btn-limpiar" data-close-modal="contacto"><i class="fas fa-xmark"></i> Cerrar</button>
		</div>
	</div>
</div>

<footer class="pd-footer">
	<div class="pd-container">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> - CVG Alcasa - Gerencia de Comunicación Estratégica</p>
		<p style="font-size:0.75rem; margin-top:0.2rem;">
			<a href="#aviso" data-open-modal="aviso">Aviso Legal</a> &nbsp;|&nbsp;
			<a href="#privacidad" data-open-modal="privacidad">Política de Privacidad</a> &nbsp;|&nbsp;
			<a href="#contacto" data-open-modal="contacto">Contacto</a>
		</p>
	</div>
</footer>

<div class="nav-botones">
	<button type="button" id="pdIrArriba" title="Ir al inicio de la página"><i class="fas fa-arrow-up"></i></button>
	<button type="button" id="pdIrAbajo" title="Ir al final de la página"><i class="fas fa-arrow-down"></i></button>
</div>

<?php wp_footer(); ?>
</body>
</html>
