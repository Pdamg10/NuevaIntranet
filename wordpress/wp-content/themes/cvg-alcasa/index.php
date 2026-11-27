<?php
/**
 * Plantilla de respaldo obligatoria del tema CVG Alcasa v2.
 *
 * @package CVG_Alcasa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main class="content-wrapper">
	<section class="page-header-box">
		<div class="page-content">
			<?php if ( have_posts() ) : ?>
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<h1><?php the_title(); ?></h1>
						<div class="entry-content">
							<?php the_content(); ?>
						</div>
					</article>
				<?php endwhile; ?>
			<?php else : ?>
				<h1><?php esc_html_e( 'Contenido no encontrado', 'cvg-alcasa' ); ?></h1>
				<p><?php esc_html_e( 'La sección solicitada no está disponible en este momento.', 'cvg-alcasa' ); ?></p>
				<p>
					<a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio' ) ); ?>" class="btn-prensa">
						<i class="fas fa-arrow-left"></i> Volver al Inicio
					</a>
				</p>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
