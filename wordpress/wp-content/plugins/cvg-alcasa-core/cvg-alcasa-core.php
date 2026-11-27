<?php
/**
 * Plugin Name: CVG Alcasa Core
 * Plugin URI: https://github.com/Pdamg10/NuevaIntranet
 * Description: Lógica de negocio, tablas MySQL ($wpdb), API REST (/wp-json/alcasa/v1/*), proxy resiliente de noticias (:5001), almacenamiento privado de CVs y panel administrativo en wp-admin para el portal CVG Alcasa v2.
 * Version: 2.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: CVG Aluminio del Caroní, S.A. (CVG Alcasa)
 * Text Domain: cvg-alcasa-core
 *
 * @package CVG_Alcasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CVG_ALCASA_CORE_VERSION', '2.0.0' );
define( 'CVG_ALCASA_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'CVG_ALCASA_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once CVG_ALCASA_CORE_PATH . 'includes/class-db.php';
require_once CVG_ALCASA_CORE_PATH . 'includes/class-cv-storage.php';
require_once CVG_ALCASA_CORE_PATH . 'includes/class-news-proxy.php';
require_once CVG_ALCASA_CORE_PATH . 'includes/class-rest-api.php';
require_once CVG_ALCASA_CORE_PATH . 'includes/class-admin-page.php';

/**
 * Resuelve la ruta en disco hacia los archivos JSON/CSV de datos compartidos (contenido, noticias, semillas).
 */
function cvg_alcasa_core_data_dir(): string {
	$candidates = array();

	if ( defined( 'ALCASA_DATA_DIR' ) && is_string( ALCASA_DATA_DIR ) && '' !== ALCASA_DATA_DIR ) {
		$candidates[] = ALCASA_DATA_DIR;
	}
	$env_dir = getenv( 'ALCASA_DATA_DIR' );
	if ( is_string( $env_dir ) && '' !== $env_dir ) {
		$candidates[] = $env_dir;
	}

	// Volumen estándar en Docker (:8080) y despliegue en Azure App Service.
	$candidates[] = ABSPATH . 'alcasa-data';
	$candidates[] = dirname( ABSPATH ) . '/alcasa-data';
	$candidates[] = CVG_ALCASA_CORE_PATH . 'data';

	foreach ( $candidates as $dir ) {
		if ( is_dir( $dir ) ) {
			return rtrim( $dir, '/\\' );
		}
	}

	return rtrim( ABSPATH . 'alcasa-data', '/\\' );
}

/**
 * Normaliza menciones institucionales a CVG Alcasa / CVG Aluminio del Caroní, S.A.
 */
function cvg_alcasa_core_normalize_brand( string $text ): string {
	if ( '' === $text ) {
		return '';
	}
	$text = (string) preg_replace( '/(?<!CVG\s)(?<!CVG-)Aluminios?\s+del\s+Caron[íi],?\s*S\.?A\.?/iu', 'CVG Aluminio del Caroní, S.A.', $text );
	$text = (string) preg_replace( '/(?<!CVG\s)(?<!CVG-)(?<![.@\/])\bAlcasa\b/u', 'CVG Alcasa', $text );
	$text = (string) preg_replace( '/\bCVG\s+CVG\s+/iu', 'CVG ', $text );
	return $text;
}

/**
 * Sanitiza un campo de texto para prevenir inyección de fórmulas en exportaciones CSV.
 */
function cvg_alcasa_core_csv_safe( string $value ): string {
	$clean = trim( preg_replace( '/[\r\n]+/', ' ', $value ) ?? '' );
	if ( '' !== $clean && in_array( $clean[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
		return "'" . $clean;
	}
	return $clean;
}

/**
 * Devuelve un hash no reversible de la IP del cliente para rate-limit y auditoría sin guardar PII cruda.
 */
function cvg_alcasa_core_client_ip_hash(): string {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$salt = defined( 'AUTH_SALT' ) ? (string) AUTH_SALT : 'cvg-alcasa-salt';
	return substr( hash_hmac( 'sha256', $ip, $salt ), 0, 24 );
}

/**
 * Hook de activación: crea/actualiza tablas MySQL, protege carpeta de CVs e importa semilla si está vacía.
 */
function cvg_alcasa_core_activate(): void {
	CVG_Alcasa_DB::install();
	CVG_Alcasa_CV_Storage::ensure_private_directory();
	CVG_Alcasa_DB::seed_contacts_if_empty();
}
register_activation_hook( __FILE__, 'cvg_alcasa_core_activate' );

/**
 * Bootstrap en plugins_loaded.
 */
function cvg_alcasa_core_boot(): void {
	CVG_Alcasa_DB::maybe_upgrade();
	CVG_Alcasa_REST_API::register();
	if ( is_admin() ) {
		CVG_Alcasa_Admin_Page::register();
	}
}
add_action( 'plugins_loaded', 'cvg_alcasa_core_boot' );
