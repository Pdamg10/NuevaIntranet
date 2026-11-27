<?php
/**
 * Template Name: Sala de Prensa
 *
 * Consulta pública de noticias oficiales "Pulso Industrial CVG Alcasa".
 *
 * @package CVG_Alcasa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$news_portal_url = cvg_alcasa_news_portal_url();
$periodico_url   = cvg_alcasa_page_url( 'periodico' );
get_header();
?>
<main class="content-wrapper">
	<div class="page-header-box">
		<img
			src="<?php echo esc_url( cvg_alcasa_media_url( 'img/banner-prensa.png' ) ); ?>"
			alt="Pulso Industrial CVG Alcasa"
			class="page-banner"
		/>

		<div class="page-content">
			<div style="text-align: center; margin-bottom: 10px;">
				<span class="prensa-kicker-badge">
					<i class="fas fa-satellite-dish"></i> Actualidad Corporativa · Sala de Prensa Oficial en Tiempo Real
				</span>
			</div>

			<h1>
				<i class="fas fa-bolt" style="color: var(--alcasa-accent); margin-right: 12px;"></i>Pulso Industrial CVG Alcasa
			</h1>

			<p class="page-intro">
				Todas las noticias oficiales, avances operativos, circulares y actividades de <strong>CVG Aluminio del Caroní, S.A. (CVG Alcasa)</strong> sincronizadas con el portal informativo con su miniatura, título, resumen, fecha y hora de publicación ·
				<a id="periodico" href="<?php echo esc_url( $periodico_url ); ?>" style="color: var(--alcasa-accent); font-weight: 700;">
					Ir al Periódico Digital <i class="fas fa-external-link-alt"></i>
				</a>
			</p>

			<div class="prensa-toolbar-row">
				<div class="prensa-api-status" id="prensaApiCounter" style="margin: 0; width: 100%;">
					<i class="fas fa-spinner fa-spin" style="color: var(--alcasa-accent);"></i>
					<span>Sincronizando noticias oficiales de Pulso Industrial CVG Alcasa...</span>
				</div>
			</div>

			<div class="ficha-modal-backdrop" id="noticiaDetailPanel" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="noticiaDetailTitle">
				<div class="ficha-modal-dialog noticia-modal-dialog">
					<div class="ficha-header">
						<div>
							<div class="ficha-kicker-row">
								<span class="ficha-kicker-badge" id="noticiaDetailCat">Gestión Industrial</span>
								<span class="ficha-kicker-en" id="noticiaDetailDatetime"></span>
							</div>
							<h4>
								<i class="fas fa-newspaper" style="color: var(--alcasa-accent-light);"></i>
								<span id="noticiaDetailTitle">Título de la noticia</span>
							</h4>
						</div>
						<button type="button" class="ficha-close-btn" id="noticiaDetailCloseBtn" aria-label="Cerrar noticia">
							<i class="fas fa-times"></i>
						</button>
					</div>
					<div class="ficha-modal-body">
						<div class="noticia-detail-body">
							<div class="noticia-detail-img-wrap">
								<img src="<?php echo esc_url( cvg_alcasa_media_url( 'img/placeholder.svg' ) ); ?>" alt="Miniatura de noticia CVG Alcasa" id="noticiaDetailImg" />
							</div>
							<div class="noticia-detail-content">
								<div class="ficha-overview-label" style="margin-bottom: 8px;">
									<i class="fas fa-file-lines"></i> Resumen del Comunicado Oficial
								</div>
								<p id="noticiaDetailText">Descripción completa del comunicado oficial de CVG Aluminio del Caroní, S.A. (CVG Alcasa).</p>
							</div>
						</div>
					</div>
					<div class="ficha-actions">
						<a href="<?php echo esc_url( $news_portal_url . '/?page=1' ); ?>" target="_blank" rel="noopener noreferrer" class="btn-ver-todos-productos" id="noticiaDetailExternalLink">
							<i class="fas fa-up-right-from-square"></i> Abrir Comunicado Oficial Completo
						</a>
						<button type="button" class="btn-modal-close" id="noticiaModalCloseFooterBtn" style="margin-left: auto;">
							<i class="fas fa-times"></i> Cerrar Vista Previa
						</button>
					</div>
				</div>
			</div>

			<div class="prensa-layout-wrapper" id="noticias" data-max="10" data-paginated="true"></div>
			<div class="prensa-pagination-bar" id="prensaPaginationBar" style="display: none;"></div>

			<div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; margin-top: 24px;">
				<button class="btn-outline-corp" id="mas" hidden>
					<i class="fas fa-plus-circle"></i> Ver archivo adicional de noticias
				</button>
				<a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'inicio' ) ); ?>" class="btn-prensa">
					<i class="fas fa-arrow-left"></i> Volver al Inicio
				</a>
			</div>

			<div class="prensa-iframe-fallback" id="prensaIframeFallback">
				<iframe data-src="<?php echo esc_url( $periodico_url ); ?>" id="prensaIframeEl" title="Periódico Digital de CVG Alcasa"></iframe>
			</div>
		</div>
	</div>
</main>
<?php
get_footer();
