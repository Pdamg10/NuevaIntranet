<?php
/**
 * Cabecera corporativa única del tema CVG Alcasa v2.
 *
 * @package CVG_Alcasa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$inicio_url       = cvg_alcasa_page_url( 'inicio' );
$productos_url    = cvg_alcasa_page_url( 'productos' );
$galeria_url      = cvg_alcasa_page_url( 'galeria' );
$prensa_url       = cvg_alcasa_page_url( 'prensa' );
$periodico_url    = cvg_alcasa_page_url( 'periodico' );
$cumpleaneros_url = cvg_alcasa_page_url( 'cumpleaneros' );
$admin_panel_url  = cvg_alcasa_admin_panel_url();

$is_inicio    = is_front_page();
$is_productos = is_page_template( 'page-productos.php' );
$is_galeria   = is_page_template( 'page-galeria.php' );
$is_prensa    = is_page_template( 'page-prensa.php' );
$is_cumple    = is_page_template( 'page-cumpleaneros.php' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php wp_head(); ?>
</head>
<body id="<?php echo esc_attr( cvg_alcasa_body_id() ); ?>" <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- HEADER CORPORATIVO ÚNICO (WordPress) -->
<div id="site-header">
	<div class="alcoa-langbar" translate="no">
		<div class="container langbar-inner">
			<span class="langbar-institucional-tag">
				<i class="fas fa-industry"></i> CVG Aluminio del Caroní, S.A. (CVG Alcasa) — Portal Institucional e Intranet
			</span>
			<div class="langbar-controls">
				<label for="alcoa-lang-select" class="alcoa-langbar-label">
					<i class="fas fa-globe"></i> Idioma:
				</label>
				<select id="alcoa-lang-select" class="alcoa-langbar-select" aria-label="Seleccionar idioma">
					<option value="es">Español</option>
					<option value="en">English</option>
					<option value="pt">Português</option>
					<option value="fr">Français</option>
					<option value="de">Deutsch</option>
					<option value="it">Italiano</option>
					<option value="ar">العربية</option>
					<option value="zh-CN">中文</option>
					<option value="ja">日本語</option>
				</select>
			</div>
		</div>
	</div>

	<header class="alcoa-header" id="mainHeader">
		<div class="container">
			<div class="header-top">
				<button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Abrir menú de navegación" aria-expanded="false">
					<i class="fas fa-bars"></i>
				</button>

				<div class="header-logo-left">
					<a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'inicio' ) ); ?>" class="header-inicio-link" title="CVG Alcasa - Inicio">
						<img
							src="<?php echo esc_url( cvg_alcasa_media_url( 'img/logo-alcasa.png' ) ); ?>"
							width="190"
							height="106"
							loading="eager"
							decoding="sync"
							alt="Logo CVG Alcasa"
							class="logo-alcasa"
						/>
					</a>
				</div>

				<div class="menu-overlay" id="menuOverlay"></div>

				<nav class="main-navigation" id="mainNav" aria-label="Navegación principal">
					<div class="mobile-drawer-header">
						<div style="display: flex; align-items: center; gap: 10px">
							<img
								src="<?php echo esc_url( cvg_alcasa_media_url( 'img/logo-alcasa.png' ) ); ?>"
								alt="CVG Alcasa"
								style="height: 42px; width: auto; object-fit: contain"
							/>
							<span class="mobile-drawer-title">CVG Alcasa</span>
						</div>
						<button class="mobile-close-btn" id="mobileCloseBtn" aria-label="Cerrar menú">
							<i class="fas fa-times"></i>
						</button>
					</div>

					<ul class="jelly-menu nav-list">
						<li class="<?php echo $is_inicio ? 'active' : ''; ?>">
							<a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'inicio' ) ); ?>" class="header-inicio-link">Inicio</a>
						</li>

						<li class="nav-item-has-dropdown">
							<a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'empresa' ) ); ?>" class="nav-link-dropdown">
								<span>Empresa</span><i class="fas fa-chevron-down dropdown-icon"></i>
							</a>
							<ul class="dropdown-menu">
								<li><a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'empresa' ) ); ?>" data-tab-target="tab-quienes-somos">¿Quiénes somos?</a></li>
								<li><a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'empresa' ) ); ?>" data-tab-target="tab-mision-vision">Misión y Visión</a></li>
								<li><a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'empresa' ) ); ?>" data-tab-target="tab-valores">Nuestros Valores</a></li>
								<li><a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'empresa' ) ); ?>" data-tab-target="tab-pilares">Pilares de Gestión</a></li>
								<li class="dropdown-divider"></li>
								<li>
									<a href="http://190.202.49.114:5050/" target="_blank" rel="noopener noreferrer">Oficina de Atención al Ciudadano</a>
								</li>
							</ul>
						</li>

						<li class="nav-item-has-dropdown <?php echo $is_productos ? 'active' : ''; ?>">
							<a href="<?php echo esc_url( $productos_url ); ?>" class="nav-link-dropdown">
								<span>Productos</span><i class="fas fa-chevron-down dropdown-icon"></i>
							</a>
							<ul class="dropdown-menu">
								<li><a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'productos' ) ); ?>">Destacados en Inicio (2)</a></li>
								<li><a href="<?php echo esc_url( $productos_url ); ?>">Catálogo Completo 2025 (33)</a></li>
								<li><a href="<?php echo esc_url( add_query_arg( 'linea', 'Fundición', $productos_url ) ); ?>">Productos de Fundición</a></li>
								<li><a href="<?php echo esc_url( add_query_arg( 'linea', 'Extrusión', $productos_url ) ); ?>">Productos de Extrusión</a></li>
								<li><a href="<?php echo esc_url( add_query_arg( 'linea', 'Laminación', $productos_url ) ); ?>">Productos de Laminación</a></li>
								<li><a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'faq' ) ); ?>">Preguntas Frecuentes (FAQ)</a></li>
							</ul>
						</li>

						<li><a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'oportunidades' ) ); ?>">Oportunidades</a></li>

						<li class="nav-item-has-dropdown <?php echo $is_prensa ? 'active' : ''; ?>">
							<a href="<?php echo esc_url( $prensa_url ); ?>" class="nav-link-dropdown">
								<span>Actualidad</span><i class="fas fa-chevron-down dropdown-icon"></i>
							</a>
							<ul class="dropdown-menu">
								<li><a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'prensa' ) ); ?>">Pulso Industrial (Top 10 Noticias)</a></li>
								<li><a href="<?php echo esc_url( $prensa_url ); ?>">Portal Pulso Industrial CVG Alcasa</a></li>
								<li><a href="<?php echo esc_url( $periodico_url ); ?>">Periódico Digital CVG Alcasa</a></li>
							</ul>
						</li>

						<li class="nav-item-has-dropdown <?php echo $is_galeria ? 'active' : ''; ?>">
							<a href="<?php echo esc_url( $galeria_url ); ?>" class="nav-link-dropdown">
								<span>Galería</span><i class="fas fa-chevron-down dropdown-icon"></i>
							</a>
							<ul class="dropdown-menu">
								<li><a href="<?php echo esc_url( $galeria_url ); ?>">Galería Completa</a></li>
								<li><a href="<?php echo esc_url( add_query_arg( 'tipo', 'videos', $galeria_url ) ); ?>">Videos de Procesos Completos (4)</a></li>
								<li><a href="<?php echo esc_url( add_query_arg( 'cat', 'Planta de Laminación', $galeria_url ) ); ?>">Planta de Laminación</a></li>
								<li><a href="<?php echo esc_url( add_query_arg( 'cat', 'Planta de Fundición', $galeria_url ) ); ?>">Planta de Fundición</a></li>
								<li><a href="<?php echo esc_url( add_query_arg( 'cat', 'Planta de Extrusión', $galeria_url ) ); ?>">Planta de Extrusión</a></li>
								<li><a href="<?php echo esc_url( add_query_arg( 'cat', 'Compromiso Social', $galeria_url ) ); ?>">Compromiso Social e Instalaciones</a></li>
								<li class="dropdown-divider"></li>
								<li><a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'galeria' ) ); ?>">Vista Previa en Inicio</a></li>
							</ul>
						</li>

						<li class="nav-item-has-dropdown <?php echo $is_cumple ? 'active' : ''; ?>">
							<a href="#" class="nav-link-dropdown">
								<span>Extranet</span><i class="fas fa-chevron-down dropdown-icon"></i>
							</a>
							<ul class="dropdown-menu dropdown-menu-right">
								<li>
									<a href="http://alcasa.com.ve/sitio/buscador_embedido.html" target="_blank" rel="noopener noreferrer">Consulta clave ARI</a>
								</li>
								<li><a href="<?php echo esc_url( $cumpleaneros_url ); ?>">Nuestros Cumpleañeros</a></li>
								<li>
									<a href="http://alcasa.com.ve:5002" target="_blank" rel="noopener noreferrer">Acceso a la mensajería institucional</a>
								</li>
								<li>
									<a href="<?php echo esc_url( $admin_panel_url ); ?>">Panel de Contactos</a>
								</li>
							</ul>
						</li>

						<li class="nav-item-cta">
							<a href="<?php echo esc_url( cvg_alcasa_page_url( 'inicio', 'contacto-directo' ) ); ?>">Contacto</a>
						</li>
					</ul>
				</nav>

				<div class="header-logo-right">
					<a href="<?php echo esc_url( $inicio_url ); ?>" class="header-inicio-link" title="Corporación Venezolana de Guayana - CVG Alcasa">
						<img
							src="<?php echo esc_url( cvg_alcasa_media_url( 'img/logo-cvg.png' ) ); ?>"
							width="180"
							height="76"
							loading="eager"
							decoding="sync"
							alt="Logo CVG y CVG Alcasa"
							class="logo-segundo logo-cvg-blanco"
						/>
					</a>
					<div class="logo-divider"></div>
					<a href="<?php echo esc_url( $inicio_url ); ?>" class="header-inicio-link" title="Gobierno Bolivariano de Venezuela">
						<img
							src="<?php echo esc_url( cvg_alcasa_media_url( 'img/logo-institucional.png' ) ); ?>"
							width="175"
							height="52"
							loading="eager"
							decoding="sync"
							alt="Logo Institucional"
							class="logo-institucional"
						/>
					</a>
				</div>
			</div>
		</div>
		<div class="header-scroll-fade" id="headerScrollFade" aria-hidden="true"></div>
	</header>
</div>
