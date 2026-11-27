<?php
/**
 * Endpoints REST de WordPress bajo /wp-json/alcasa/v1/.
 * Reemplaza api/contacto.php, api/cv.php, api/noticias.php y añade /contenido, /cumpleaneros y /health.
 *
 * @package CVG_Alcasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CVG_Alcasa_REST_API {

	const NAMESPACE_V1   = 'alcasa/v1';
	const RATE_LIMIT_MAX = 12;
	const RATE_LIMIT_TTL = 600; // 10 minutos

	public static function register(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes(): void {
		register_rest_route(
			self::NAMESPACE_V1,
			'/contacto',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'handle_contacto' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/cv',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'handle_cv' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/noticias',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'handle_noticias' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/contenido',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'handle_contenido' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/cumpleaneros',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'handle_cumpleaneros' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/health',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'handle_health' ),
				'permission_callback' => static function (): bool {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	private static function check_rate_limit( string $bucket ): bool {
		$key   = 'alcasa_rl_' . $bucket . '_' . cvg_alcasa_core_client_ip_hash();
		$count = (int) get_transient( $key );
		if ( $count >= self::RATE_LIMIT_MAX ) {
			return false;
		}
		set_transient( $key, $count + 1, self::RATE_LIMIT_TTL );
		return true;
	}

	private static function maybe_notify( string $const_name, string $subject, string $body ): void {
		$to = defined( $const_name ) ? (string) constant( $const_name ) : '';
		if ( '' === $to ) {
			$env = getenv( $const_name );
			$to  = is_string( $env ) ? $env : '';
		}
		$to = sanitize_email( $to );
		if ( '' !== $to && is_email( $to ) ) {
			wp_mail( $to, $subject, $body );
		}
	}

	public static function handle_contacto( WP_REST_Request $request ): WP_REST_Response {
		if ( ! self::check_rate_limit( 'contacto' ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => 'Has enviado demasiadas solicitudes en pocos minutos. Intenta nuevamente más tarde.',
				),
				429
			);
		}

		$nombre     = mb_substr( sanitize_text_field( (string) $request->get_param( 'nombre' ) ), 0, 120 );
		$correo     = sanitize_email( (string) $request->get_param( 'correo' ) );
		$empresa    = mb_substr( sanitize_text_field( (string) $request->get_param( 'empresa' ) ), 0, 120 );
		$interes    = mb_substr( sanitize_text_field( (string) $request->get_param( 'interes' ) ), 0, 160 );
		$comentario = mb_substr( sanitize_textarea_field( (string) $request->get_param( 'comentario' ) ), 0, 2000 );

		if ( '' === $nombre || ! is_email( $correo ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => 'Nombre y correo electrónico válidos son obligatorios.',
				),
				400
			);
		}

		$id = CVG_Alcasa_DB::insert_contacto(
			array(
				'nombre'     => $nombre,
				'correo'     => $correo,
				'empresa'    => $empresa,
				'interes'    => $interes,
				'comentario' => $comentario,
				'ip_hash'    => cvg_alcasa_core_client_ip_hash(),
			)
		);

		if ( $id <= 0 ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => 'No se pudo registrar el mensaje en la base de datos. Intenta nuevamente.',
				),
				500
			);
		}

		self::maybe_notify(
			'ALCASA_MAIL_CONTACTO',
			'Nuevo contacto web - CVG Alcasa',
			"Registro #{$id}\nNombre: {$nombre}\nCorreo: {$correo}\nEmpresa: {$empresa}\nInterés: {$interes}\nComentario: {$comentario}"
		);

		return new WP_REST_Response(
			array(
				'success' => true,
				'id'      => $id,
				'mensaje' => '¡Gracias! Hemos recibido tu mensaje. Nos pondremos en contacto contigo a la brevedad.',
			),
			200
		);
	}

	public static function handle_cv( WP_REST_Request $request ): WP_REST_Response {
		if ( ! self::check_rate_limit( 'cv' ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => 'Has enviado demasiadas postulaciones en pocos minutos. Intenta nuevamente más tarde.',
				),
				429
			);
		}

		$nombre   = mb_substr( sanitize_text_field( (string) $request->get_param( 'nombre' ) ), 0, 120 );
		$cedula   = mb_substr( sanitize_text_field( (string) $request->get_param( 'cedula' ) ), 0, 24 );
		$correo   = sanitize_email( (string) $request->get_param( 'correo' ) );
		$telefono = mb_substr( sanitize_text_field( (string) $request->get_param( 'telefono' ) ), 0, 40 );
		$nivel    = mb_substr( sanitize_text_field( (string) $request->get_param( 'nivel' ) ), 0, 80 );
		$area     = mb_substr( sanitize_text_field( (string) $request->get_param( 'area' ) ), 0, 120 );
		$resumen  = mb_substr( sanitize_textarea_field( (string) $request->get_param( 'resumen' ) ), 0, 1200 );

		if ( '' === $nombre || '' === $cedula || '' === $telefono || '' === $nivel || '' === $area || ! is_email( $correo ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => 'Por favor completa todos los campos obligatorios (*).',
				),
				400
			);
		}

		$files = $request->get_file_params();
		$file  = isset( $files['cv_archivo'] ) && is_array( $files['cv_archivo'] ) ? $files['cv_archivo'] : array();
		$saved = CVG_Alcasa_CV_Storage::store_upload( $file );

		if ( is_wp_error( $saved ) ) {
			$status = (int) ( $saved->get_error_data()['status'] ?? 400 );
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => $saved->get_error_message(),
				),
				$status
			);
		}

		$id = CVG_Alcasa_DB::insert_postulacion(
			array(
				'nombre'           => $nombre,
				'cedula'           => $cedula,
				'correo'           => $correo,
				'telefono'         => $telefono,
				'nivel'            => $nivel,
				'area'             => $area,
				'resumen'          => $resumen,
				'archivo'          => $saved['archivo'],
				'archivo_original' => $saved['archivo_original'],
				'archivo_tamano'   => $saved['archivo_tamano'],
				'ip_hash'          => cvg_alcasa_core_client_ip_hash(),
			)
		);

		if ( $id <= 0 ) {
			CVG_Alcasa_CV_Storage::delete_file( $saved['archivo'] );
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => 'No se pudo registrar la postulación en la base de datos.',
				),
				500
			);
		}

		self::maybe_notify(
			'ALCASA_MAIL_RRHH',
			'Nueva postulación (CV) - CVG Alcasa',
			"Registro #{$id}\nNombre: {$nombre} ({$cedula})\nÁrea: {$area}\nNivel: {$nivel}\nArchivo: {$saved['archivo']}"
		);

		return new WP_REST_Response(
			array(
				'success' => true,
				'id'      => $id,
				'archivo' => $saved['archivo'],
				'mensaje' => "¡Postulación recibida con éxito! Tu Síntesis Curricular ha sido registrada para el área de {$area}.",
			),
			200
		);
	}

	public static function handle_noticias( WP_REST_Request $request ): WP_REST_Response {
		$req_id   = trim( (string) $request->get_param( 'id' ) );
		$no_cache = ! empty( $request->get_param( 'nocache' ) );

		if ( '' !== $req_id ) {
			$detail = CVG_Alcasa_News_Proxy::get_detail( $req_id, $no_cache );
			if ( null === $detail ) {
				return new WP_REST_Response( array( 'error' => 'Noticia no encontrada' ), 404 );
			}
			return new WP_REST_Response( $detail, 200 );
		}

		$data = CVG_Alcasa_News_Proxy::get_list(
			array(
				'page'    => $request->get_param( 'page' ),
				'q'       => $request->get_param( 'q' ),
				'fecha'   => $request->get_param( 'fecha' ),
				'mes'     => $request->get_param( 'mes' ),
				'anio'    => $request->get_param( 'anio' ),
				'nocache' => $no_cache,
			)
		);

		return new WP_REST_Response( $data, 200 );
	}

	public static function handle_contenido(): WP_REST_Response {
		$file = cvg_alcasa_core_data_dir() . '/contenido.json';
		if ( ! is_readable( $file ) ) {
			return new WP_REST_Response( array( 'error' => 'contenido.json no disponible' ), 404 );
		}
		$raw  = file_get_contents( $file );
		$json = false !== $raw ? json_decode( $raw, true ) : null;
		if ( ! is_array( $json ) ) {
			return new WP_REST_Response( array( 'error' => 'contenido.json inválido' ), 500 );
		}
		return new WP_REST_Response( $json, 200 );
	}

	public static function handle_cumpleaneros( WP_REST_Request $request ): WP_REST_Response {
		$file = cvg_alcasa_core_data_dir() . '/cumpleaneros.json';
		if ( ! is_readable( $file ) ) {
			return new WP_REST_Response(
				array(
					'mensaje'      => array(
						'titulo' => '¡Feliz Cumpleaños te desea CVG Alcasa!',
						'texto'  => 'Base de cumpleañeros pendiente de carga en el servidor.',
					),
					'cumpleaneros' => array(),
				),
				200
			);
		}

		$raw  = file_get_contents( $file );
		$json = false !== $raw ? json_decode( $raw, true ) : null;
		if ( ! is_array( $json ) ) {
			return new WP_REST_Response( array( 'error' => 'cumpleaneros.json inválido' ), 500 );
		}

		$dia = (int) $request->get_param( 'dia' );
		$mes = (int) $request->get_param( 'mes' );
		if ( $mes >= 1 && $mes <= 12 && isset( $json['cumpleaneros'] ) && is_array( $json['cumpleaneros'] ) ) {
			$json['cumpleaneros'] = array_values(
				array_filter(
					$json['cumpleaneros'],
					static function ( $p ) use ( $dia, $mes ): bool {
						if ( ! is_array( $p ) ) {
							return false;
						}
						if ( (int) ( $p['mes'] ?? 0 ) !== $mes ) {
							return false;
						}
						return ( 0 === $dia ) || ( (int) ( $p['dia'] ?? 0 ) === $dia );
					}
				)
			);
		}

		return new WP_REST_Response( $json, 200 );
	}

	public static function handle_health(): WP_REST_Response {
		$counts  = CVG_Alcasa_DB::counts();
		$storage = CVG_Alcasa_CV_Storage::health_status();
		$news    = CVG_Alcasa_News_Proxy::check_connectivity();

		return new WP_REST_Response(
			array(
				'plugin_version' => CVG_ALCASA_CORE_VERSION,
				'php_version'    => PHP_VERSION,
				'db'             => array(
					'ok'            => true,
					'contactos'     => $counts['contactos'],
					'postulaciones' => $counts['postulaciones'],
				),
				'cv_storage'     => $storage,
				'news_upstream'  => $news,
				'data_dir_ready' => is_readable( cvg_alcasa_core_data_dir() . '/contenido.json' ),
			),
			200
		);
	}
}
