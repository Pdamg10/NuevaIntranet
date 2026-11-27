<?php
/**
 * Almacenamiento privado y validación estricta de Síntesis Curriculares (PDF/DOC/DOCX <= 10 MB).
 * Nunca expone archivos por HTTP directo; las descargas pasan por admin-post.php con manage_options.
 *
 * @package CVG_Alcasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CVG_Alcasa_CV_Storage {

	const MAX_BYTES = 10485760; // 10 MB

	const ALLOWED_MIMES = array(
		'pdf'  => array( 'application/pdf' ),
		'doc'  => array( 'application/msword', 'application/octet-stream', 'application/vnd.ms-office' ),
		'docx' => array(
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/zip',
			'application/octet-stream',
		),
	);

	/**
	 * Devuelve el directorio privado de almacenamiento de CVs (fuera de la raíz web cuando es posible).
	 */
	public static function base_dir(): string {
		$root = '';
		if ( defined( 'ALCASA_PRIVATE_STORAGE' ) && is_string( ALCASA_PRIVATE_STORAGE ) && '' !== ALCASA_PRIVATE_STORAGE ) {
			$root = ALCASA_PRIVATE_STORAGE;
		} else {
			$env = getenv( 'ALCASA_PRIVATE_STORAGE' );
			if ( is_string( $env ) && '' !== $env ) {
				$root = $env;
			}
		}

		// En Azure App Service Linux, /home/site es persistente y está fuera de /home/site/wwwroot.
		if ( '' === $root && is_dir( '/home/site' ) && is_writable( '/home/site' ) ) {
			$root = '/home/site/alcasa-private';
		}

		if ( '' === $root ) {
			$uploads = wp_upload_dir();
			$root    = rtrim( (string) ( $uploads['basedir'] ?? ( WP_CONTENT_DIR . '/uploads' ) ), '/\\' ) . '/alcasa-private';
		}

		return rtrim( $root, '/\\' ) . '/cv';
	}

	/**
	 * Crea y protege el directorio privado (.htaccess + index.php en caso de fallback dentro de uploads).
	 */
	public static function ensure_private_directory(): bool {
		$dir = self::base_dir();
		if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
			return false;
		}

		$parent   = dirname( $dir );
		$htaccess = $parent . '/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			$rules = "# Bloqueo de acceso HTTP directo a archivos privados de CVG Alcasa\n"
				. "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n"
				. "<IfModule !mod_authz_core.c>\n    Order deny,allow\n    Deny from all\n</IfModule>\n";
			@file_put_contents( $htaccess, $rules );
		}

		foreach ( array( $parent, $dir ) as $d ) {
			$idx = $d . '/index.php';
			if ( ! file_exists( $idx ) ) {
				@file_put_contents( $idx, "<?php\n// Silencio.\n" );
			}
		}

		return is_dir( $dir ) && is_writable( $dir );
	}

	/**
	 * Valida y guarda un archivo subido en $_FILES['cv_archivo'].
	 *
	 * @param array $file Elemento de $_FILES.
	 * @return array|WP_Error Datos del archivo guardado o WP_Error.
	 */
	public static function store_upload( array $file ) {
		if ( empty( $file ) || ! isset( $file['error'] ) || UPLOAD_ERR_OK !== (int) $file['error'] ) {
			return new WP_Error( 'cv_missing', 'Por favor adjunta tu síntesis curricular (PDF, DOC o DOCX).', array( 'status' => 400 ) );
		}

		$size = isset( $file['size'] ) ? (int) $file['size'] : 0;
		if ( $size <= 0 || $size > self::MAX_BYTES ) {
			return new WP_Error( 'cv_size', 'El archivo supera el límite máximo permitido de 10 MB.', array( 'status' => 400 ) );
		}

		$orig_name = sanitize_file_name( (string) ( $file['name'] ?? '' ) );
		$ext       = strtolower( pathinfo( $orig_name, PATHINFO_EXTENSION ) );
		if ( ! isset( self::ALLOWED_MIMES[ $ext ] ) ) {
			return new WP_Error( 'cv_ext', 'Formato no permitido. Solo se admiten archivos PDF, DOC o DOCX.', array( 'status' => 400 ) );
		}

		$tmp = (string) ( $file['tmp_name'] ?? '' );
		if ( '' === $tmp || ! is_uploaded_file( $tmp ) ) {
			return new WP_Error( 'cv_invalid', 'Archivo de carga inválido.', array( 'status' => 400 ) );
		}

		$mime = '';
		if ( class_exists( 'finfo' ) ) {
			$finfo = new finfo( FILEINFO_MIME_TYPE );
			$mime  = (string) $finfo->file( $tmp );
		} elseif ( ! empty( $file['type'] ) ) {
			$mime = (string) $file['type'];
		}

		if ( '' !== $mime && ! in_array( $mime, self::ALLOWED_MIMES[ $ext ], true ) ) {
			return new WP_Error( 'cv_mime', 'El contenido del archivo no corresponde a un documento PDF, DOC o DOCX válido.', array( 'status' => 400 ) );
		}

		if ( ! self::ensure_private_directory() ) {
			return new WP_Error( 'cv_storage', 'No se pudo inicializar el almacenamiento privado de archivos.', array( 'status' => 500 ) );
		}

		try {
			$rand = bin2hex( random_bytes( 4 ) );
		} catch ( Exception $e ) {
			$rand = substr( wp_generate_password( 8, false, false ), 0, 8 );
		}

		$safe_name = gmdate( 'Ymd-His' ) . '-' . strtolower( $rand ) . '.' . $ext;
		$dest      = self::base_dir() . '/' . $safe_name;

		if ( ! @move_uploaded_file( $tmp, $dest ) ) {
			return new WP_Error( 'cv_move', 'No se pudo guardar el archivo en el servidor.', array( 'status' => 500 ) );
		}

		@chmod( $dest, 0640 );

		return array(
			'archivo'          => $safe_name,
			'archivo_original' => $orig_name,
			'archivo_tamano'   => $size,
		);
	}

	/**
	 * Resuelve de forma segura la ruta absoluta de un CV (protegido contra path traversal).
	 */
	public static function resolve_safe_path( string $filename ): ?string {
		$base = basename( $filename );
		if ( ! preg_match( '/^[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.(pdf|docx?)$/i', $base ) ) {
			return null;
		}

		$dir_real  = realpath( self::base_dir() );
		$file_real = realpath( self::base_dir() . '/' . $base );

		if ( false === $dir_real || false === $file_real ) {
			return null;
		}
		if ( strpos( $file_real, $dir_real . DIRECTORY_SEPARATOR ) !== 0 ) {
			return null;
		}
		if ( ! is_file( $file_real ) || ! is_readable( $file_real ) ) {
			return null;
		}

		return $file_real;
	}

	public static function delete_file( string $filename ): bool {
		$path = self::resolve_safe_path( $filename );
		if ( null === $path ) {
			return false;
		}
		return @unlink( $path );
	}

	public static function health_status(): array {
		$dir = self::base_dir();
		$ok  = self::ensure_private_directory();
		$outside_wwwroot = ( strpos( str_replace( '\\', '/', $dir ), str_replace( '\\', '/', ABSPATH ) ) !== 0 );

		return array(
			'writable'        => $ok,
			'outside_wwwroot' => $outside_wwwroot,
			'storage_mode'    => $outside_wwwroot ? 'private-outside-wwwroot' : 'uploads-htaccess-protected',
		);
	}
}
