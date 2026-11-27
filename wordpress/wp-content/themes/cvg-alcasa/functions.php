<?php
/**
 * Funciones del tema CVG Alcasa v2 (exclusivamente capa de presentación y encolado).
 * La lógica de negocio, tablas MySQL y endpoints REST viven en el plugin cvg-alcasa-core.
 *
 * @package CVG_Alcasa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CVG_ALCASA_THEME_VERSION', '2.0.0' );

/**
 * Configuración base del tema WordPress.
 */
function cvg_alcasa_theme_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	register_nav_menus(
		array(
			'primary' => __( 'Menú Principal Institucional', 'cvg-alcasa' ),
		)
	);
}
add_action( 'after_setup_theme', 'cvg_alcasa_theme_setup' );

/**
 * Resuelve la URL base para archivos multimedia compartidos.
 * En local apunta a /alcasa-media (volumen Docker). En Azure puede apuntar a Azure Blob Storage / Front Door.
 */
function cvg_alcasa_media_base_url(): string {
	$base = defined( 'ALCASA_MEDIA_BASE_URL' ) ? (string) ALCASA_MEDIA_BASE_URL : '';
	if ( '' === $base ) {
		$env = getenv( 'ALCASA_MEDIA_BASE_URL' );
		$base = is_string( $env ) ? $env : '';
	}
	if ( '' === $base ) {
		$base = '/alcasa-media';
	}
	return rtrim( $base, '/' );
}

/**
 * Construye la URL final de un recurso multimedia (img/... o media/...).
 */
function cvg_alcasa_media_url( string $relative_path ): string {
	$clean = ltrim( $relative_path, '/' );
	if ( strpos( $clean, 'assets/' ) === 0 ) {
		$clean = substr( $clean, strlen( 'assets/' ) );
	}
	return cvg_alcasa_media_base_url() . '/' . $clean;
}

/**
 * Resuelve la URL base para los archivos JSON de datos estáticos/respaldo.
 */
function cvg_alcasa_data_base_url(): string {
	$base = defined( 'ALCASA_DATA_BASE_URL' ) ? (string) ALCASA_DATA_BASE_URL : '';
	if ( '' === $base ) {
		$env = getenv( 'ALCASA_DATA_BASE_URL' );
		$base = is_string( $env ) ? $env : '';
	}
	if ( '' === $base ) {
		$base = '/alcasa-data';
	}
	return rtrim( $base, '/' );
}

/**
 * URL del portal externo de noticias (:5001).
 */
function cvg_alcasa_news_portal_url(): string {
	$url = defined( 'ALCASA_NEWS_URL' ) ? (string) ALCASA_NEWS_URL : '';
	if ( '' === $url ) {
		$env = getenv( 'ALCASA_NEWS_URL' );
		$url = is_string( $env ) ? $env : '';
	}
	if ( '' === $url ) {
		$url = 'http://alcasa.com.ve:5001';
	}
	return rtrim( $url, '/' );
}

/**
 * Resuelve la URL de una página institucional de WordPress a partir de su slug.
 */
function cvg_alcasa_page_url( string $slug, string $hash = '' ): string {
	if ( 'inicio' === $slug || '' === $slug ) {
		$url = home_url( '/' );
	} else {
		$page = get_page_by_path( $slug );
		$url  = $page ? get_permalink( $page ) : home_url( '/' . trim( $slug, '/' ) . '/' );
	}

	if ( '' !== $hash ) {
		$url .= '#' . ltrim( $hash, '#' );
	}

	return $url;
}

/**
 * URL del panel administrativo de Contactos y Postulaciones en wp-admin.
 */
function cvg_alcasa_admin_panel_url(): string {
	if ( defined( 'CVG_ALCASA_CORE_VERSION' ) ) {
		return admin_url( 'admin.php?page=cvg-alcasa-bandejas' );
	}
	return admin_url();
}

/**
 * Devuelve el ID semántico de <body> esperado por main.css y app.js según la plantilla activa.
 */
function cvg_alcasa_body_id(): string {
	if ( is_front_page() ) {
		return 'inicio';
	}
	if ( is_page_template( 'page-productos.php' ) ) {
		return 'pagina-productos';
	}
	if ( is_page_template( 'page-galeria.php' ) ) {
		return 'pagina-galeria';
	}
	if ( is_page_template( 'page-prensa.php' ) ) {
		return 'pagina-prensa';
	}
	if ( is_page_template( 'page-cumpleaneros.php' ) ) {
		return 'pagina-cumpleanos';
	}
	return 'pagina-alcasa';
}

/**
 * Calcula versión de caché basada en filemtime para evitar caché obsoleta.
 */
function cvg_alcasa_asset_version( string $relative_path ): string {
	$full = get_template_directory() . '/' . ltrim( $relative_path, '/' );
	return file_exists( $full ) ? (string) filemtime( $full ) : CVG_ALCASA_THEME_VERSION;
}

/**
 * Construye el payload de configuración inyectado al frontend vía wp_localize_script.
 */
function cvg_alcasa_frontend_config(): array {
	$plugin_active = defined( 'CVG_ALCASA_CORE_VERSION' );
	$rest_base     = rtrim( rest_url( 'alcasa/v1' ), '/' );
	$data_base     = cvg_alcasa_data_base_url();

	return array(
		'isWordPress'         => true,
		'pluginActive'        => $plugin_active,
		'homeUrl'             => rtrim( home_url( '/' ), '/' ),
		'themeUrl'            => get_template_directory_uri(),
		'mediaBaseUrl'        => cvg_alcasa_media_base_url(),
		'dataBaseUrl'         => $data_base,
		'restUrl'             => $rest_base,
		'nonce'               => wp_create_nonce( 'wp_rest' ),
		'newsPortalUrl'       => cvg_alcasa_news_portal_url(),
		'contenidoUrl'        => $plugin_active ? ( $rest_base . '/contenido' ) : ( $data_base . '/contenido.json' ),
		'noticiasUrl'         => $plugin_active ? ( $rest_base . '/noticias' ) : '',
		'noticiasFallbackUrl' => $data_base . '/noticias.json',
		'contactoUrl'         => $plugin_active ? ( $rest_base . '/contacto' ) : '',
		'cvUrl'               => $plugin_active ? ( $rest_base . '/cv' ) : '',
		'cumpleanerosDataUrl' => $plugin_active ? ( $rest_base . '/cumpleaneros' ) : ( $data_base . '/cumpleaneros.json' ),
		'pages'               => array(
			'inicio'       => cvg_alcasa_page_url( 'inicio' ),
			'productos'    => cvg_alcasa_page_url( 'productos' ),
			'galeria'      => cvg_alcasa_page_url( 'galeria' ),
			'prensa'       => cvg_alcasa_page_url( 'prensa' ),
			'periodico'    => cvg_alcasa_page_url( 'periodico' ),
			'cumpleaneros' => cvg_alcasa_page_url( 'cumpleaneros' ),
			'adminPanel'   => cvg_alcasa_admin_panel_url(),
		),
	);
}

/**
 * Encola hojas de estilo y scripts siguiendo el estándar de WordPress.
 */
function cvg_alcasa_enqueue_assets(): void {
	$theme_uri = get_template_directory_uri();

	wp_enqueue_style(
		'cvg-alcasa-google-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'cvg-alcasa-fontawesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
		array(),
		'6.4.0'
	);

	wp_enqueue_style(
		'cvg-alcasa-main',
		$theme_uri . '/assets/css/main.css',
		array( 'cvg-alcasa-google-fonts', 'cvg-alcasa-fontawesome' ),
		cvg_alcasa_asset_version( 'assets/css/main.css' )
	);

	// Plantilla autónoma Periódico Digital: carga su hoja y script dedicados sin app.js.
	if ( is_page_template( 'page-periodico.php' ) ) {
		wp_enqueue_style(
			'cvg-alcasa-periodico',
			$theme_uri . '/assets/css/periodico.css',
			array( 'cvg-alcasa-main' ),
			cvg_alcasa_asset_version( 'assets/css/periodico.css' )
		);

		wp_enqueue_script(
			'cvg-alcasa-periodico',
			$theme_uri . '/assets/js/periodico.js',
			array(),
			cvg_alcasa_asset_version( 'assets/js/periodico.js' ),
			true
		);

		wp_localize_script( 'cvg-alcasa-periodico', 'ALCASA_WP', cvg_alcasa_frontend_config() );
		return;
	}

	// Motor principal del portal en el resto de vistas.
	wp_enqueue_script(
		'cvg-alcasa-app',
		$theme_uri . '/assets/js/app.js',
		array(),
		cvg_alcasa_asset_version( 'assets/js/app.js' ),
		true
	);

	wp_localize_script( 'cvg-alcasa-app', 'ALCASA_WP', cvg_alcasa_frontend_config() );

	// Recursos específicos del módulo Nuestros Cumpleañeros.
	if ( is_page_template( 'page-cumpleaneros.php' ) ) {
		wp_enqueue_style(
			'cvg-alcasa-cumpleanos',
			$theme_uri . '/assets/css/cumpleanos.css',
			array( 'cvg-alcasa-main' ),
			cvg_alcasa_asset_version( 'assets/css/cumpleanos.css' )
		);

		wp_enqueue_script(
			'canvas-confetti',
			'https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js',
			array(),
			'1.5.1',
			true
		);

		wp_enqueue_script(
			'cvg-alcasa-cumpleanos',
			$theme_uri . '/assets/js/cumpleanos.js',
			array( 'cvg-alcasa-app', 'canvas-confetti' ),
			cvg_alcasa_asset_version( 'assets/js/cumpleanos.js' ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'cvg_alcasa_enqueue_assets' );

/**
 * Favicon institucional desde media compartida.
 */
function cvg_alcasa_head_icons(): void {
	$favicon_png = esc_url( cvg_alcasa_media_url( 'img/favicon.png' ) );
	echo '<link rel="icon" type="image/png" href="' . $favicon_png . '">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . $favicon_png . '">' . "\n";
}
add_action( 'wp_head', 'cvg_alcasa_head_icons', 2 );
