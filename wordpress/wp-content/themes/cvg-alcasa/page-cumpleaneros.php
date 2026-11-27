<?php
/**
 * Template Name: Nuestros Cumpleañeros
 *
 * Directorio de cumpleaños del personal de CVG Alcasa con tarjeta descargable PNG/PDF.
 *
 * @package CVG_Alcasa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main class="content-wrapper cumple-main">
	<h1 class="cumple-sr">Cumpleañeros CVG Alcasa</h1>

	<section class="cumple-filtros" aria-label="Buscar cumpleañeros">
		<select id="cumple-dia" aria-label="Día"></select>
		<select id="cumple-mes" aria-label="Mes"></select>
		<button type="button" id="cumple-consultar" class="cumple-btn-consultar">Consultar</button>
	</section>

	<section class="cumple-mensaje" id="cumple-mensaje" hidden>
		<h2>
			<span class="cumple-emoji">🎂</span>
			<span class="cumple-titulo-grad" id="cumple-titulo">¡Feliz Cumpleaños te desea CVG Alcasa!</span>
			<span class="cumple-emoji">🎂</span>
		</h2>
		<p class="cumple-msg-personal" id="cumple-texto"></p>
		<p class="cumple-hint">
			<i class="fas fa-hand-pointer"></i> Haz clic en cualquier tarjeta para generar la felicitación personalizada y descargarla.
		</p>
	</section>

	<section id="cumple-grid" class="cumple-grid" aria-live="polite" aria-label="Cumpleañeros"></section>
</main>

<div class="cumple-modal" id="cumple-modal" role="dialog" aria-modal="true" aria-label="Tarjeta de cumpleaños" aria-hidden="true">
	<div class="cumple-modal-caja">
		<button type="button" class="cumple-modal-cerrar" id="cumple-cerrar" aria-label="Cerrar">
			<i class="fas fa-xmark"></i>
		</button>

		<div class="cumple-tarjeta" id="cumple-tarjeta">
			<img class="ct-fondo" src="<?php echo esc_url( cvg_alcasa_media_url( 'img/plantilla-cumpleanos.png' ) ); ?>" alt="" />
			<img class="ct-foto" id="ct-foto" alt="" />
			<div class="ct-texto">
				<h2 class="ct-nombre" id="ct-nombre"></h2>
				<span class="ct-ficha" id="ct-ficha"></span>
				<div class="ct-info">
					<div><b>DEPARTAMENTO</b><span id="ct-depto"></span></div>
					<div><b>GERENCIA</b><span id="ct-gcia"></span></div>
				</div>
				<p class="ct-msg">
					En este día tan especial, <strong id="ct-fecha"></strong>, CVG Aluminio del Caroní, S.A. se une a la celebración de tu cumpleaños y te desea un año lleno de salud, éxitos y bendiciones. ¡Gracias por tu valiosa labor y compromiso!
				</p>
			</div>
		</div>

		<div class="cumple-modal-acciones">
			<button type="button" class="cumple-btn-descarga" id="cumple-btn-img">
				<i class="fas fa-image"></i> Descargar imagen
			</button>
			<button type="button" class="cumple-btn-descarga cumple-btn-pdf" id="cumple-btn-pdf">
				<i class="fas fa-file-pdf"></i> Descargar PDF
			</button>
		</div>
	</div>
</div>
<?php
get_footer();
