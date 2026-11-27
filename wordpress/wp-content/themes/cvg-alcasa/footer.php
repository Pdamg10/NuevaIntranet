<?php
/**
 * Pie de página corporativo único del tema CVG Alcasa v2.
 *
 * @package CVG_Alcasa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$productos_url = cvg_alcasa_page_url( 'productos' );
$prensa_url    = cvg_alcasa_page_url( 'prensa' );
?>
<div id="site-footer">
	<footer id="contacto">
		<div class="container">
			<div class="footer-grid">
				<div class="footer-col">
					<h4>CVG Aluminio del Caroní, S.A.</h4>
					<p>
						CVG Aluminio del Caroní, S.A. (CVG Alcasa)<br />
						Zona Industrial Matanzas, Avenida Fuerzas Armadas,<br />
						Ciudad Guayana, Estado Bolívar, Venezuela.
					</p>
					<div class="social-links">
						<a href="https://www.facebook.com/Alcasa.CVG/?locale=es_LA" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
						<a href="https://www.instagram.com/alcasa_cvg/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
						<a href="https://www.youtube.com/@alcasaaluminiodelcaronis.a8220" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
						<a href="https://www.tiktok.com/@cvg_alcasa" target="_blank" rel="noopener noreferrer" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
					</div>
				</div>

				<div class="footer-col">
					<h4>Empresa</h4>
					<ul>
						<li><a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'empresa' ) ); ?>" data-tab-target="tab-quienes-somos"><i class="fas fa-chevron-right"></i> Quiénes somos</a></li>
						<li><a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'empresa' ) ); ?>" data-tab-target="tab-mision-vision"><i class="fas fa-chevron-right"></i> Misión y Visión</a></li>
						<li><a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'empresa' ) ); ?>" data-tab-target="tab-valores"><i class="fas fa-chevron-right"></i> Valores</a></li>
						<li><a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'oportunidades' ) ); ?>"><i class="fas fa-chevron-right"></i> Oportunidades Laborales</a></li>
						<li><a href="<?php echo esc_url( $prensa_url ); ?>"><i class="fas fa-chevron-right"></i> Pulso Industrial CVG Alcasa</a></li>
					</ul>
				</div>

				<div class="footer-col">
					<h4>Líneas de Productos</h4>
					<ul>
						<li><a href="<?php echo esc_url( $productos_url ); ?>"><i class="fas fa-chevron-right"></i> Catálogo Completo (33)</a></li>
						<li><a href="<?php echo esc_url( add_query_arg( 'linea', 'Fundición', $productos_url ) ); ?>"><i class="fas fa-chevron-right"></i> Productos de Fundición (2)</a></li>
						<li><a href="<?php echo esc_url( add_query_arg( 'linea', 'Extrusión', $productos_url ) ); ?>"><i class="fas fa-chevron-right"></i> Productos de Extrusión (24)</a></li>
						<li><a href="<?php echo esc_url( add_query_arg( 'linea', 'Laminación', $productos_url ) ); ?>"><i class="fas fa-chevron-right"></i> Productos de Laminación (7)</a></li>
						<li><a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'faq' ) ); ?>"><i class="fas fa-chevron-right"></i> Preguntas Frecuentes</a></li>
					</ul>
				</div>

				<div class="footer-col">
					<h4>Contacto Oficial</h4>
					<ul>
						<li><i class="fas fa-map-marker-alt" style="color: var(--alcasa-accent-light)"></i> Zona Industrial Matanzas, Ciudad Guayana</li>
						<li><i class="fas fa-clock" style="color: var(--alcasa-accent-light)"></i> Lun - Vie: 7:00am – 4:00pm</li>
						<li><i class="fas fa-phone" style="color: var(--alcasa-accent-light)"></i> +58 (0286) 9801476 / +58 (0412) 2522272</li>
						<li><i class="fas fa-envelope" style="color: var(--alcasa-accent-light)"></i> comercializacion@alcasa.com.ve</li>
						<li><i class="fas fa-id-card" style="color: var(--alcasa-accent-light)"></i> RIF: G-20110929-0</li>
					</ul>
				</div>
			</div>

			<div class="footer-bottom">
				<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> - CVG Aluminio del Caroní, S.A. (CVG Alcasa). Todos los derechos reservados. | RIF: G-20110929-0</p>
			</div>
		</div>
	</footer>
</div>

<a
	href="https://wa.me/584122522272?text=Hola%2C%20vengo%20de%20la%20p%C3%A1gina%20web%20de%20CVG%20ALCASA%20y%20quisiera%20informaci%C3%B3n%20sobre%20sus%20productos."
	target="_blank"
	rel="noopener noreferrer"
	class="whatsapp-float"
	aria-label="Contactar a CVG Alcasa por WhatsApp"
>
	<i class="fab fa-whatsapp"></i>
	<span class="whatsapp-tooltip">Contáctanos por WhatsApp</span>
</a>

<?php wp_footer(); ?>
</body>
</html>
