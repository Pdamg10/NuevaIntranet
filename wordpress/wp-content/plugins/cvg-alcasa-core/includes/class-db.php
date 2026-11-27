<?php
/**
 * Capa de persistencia MySQL ($wpdb + dbDelta) para Contactos Comerciales y Postulaciones (CV).
 *
 * @package CVG_Alcasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CVG_Alcasa_DB {

	const DB_VERSION_OPTION = 'cvg_alcasa_db_version';
	const DB_VERSION        = '1.0.0';

	public static function table_contactos(): string {
		global $wpdb;
		return $wpdb->prefix . 'alcasa_contactos';
	}

	public static function table_postulaciones(): string {
		global $wpdb;
		return $wpdb->prefix . 'alcasa_postulaciones';
	}

	/**
	 * Crea o actualiza las tablas del portal en MySQL usando dbDelta().
	 */
	public static function install(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$t_contactos     = self::table_contactos();
		$t_postulaciones = self::table_postulaciones();

		$sql_contactos = "CREATE TABLE {$t_contactos} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			fecha datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			nombre varchar(120) NOT NULL,
			correo varchar(120) NOT NULL,
			empresa varchar(120) NOT NULL DEFAULT '',
			interes varchar(160) NOT NULL DEFAULT '',
			comentario text NOT NULL,
			ip_hash varchar(64) NOT NULL DEFAULT '',
			estado varchar(32) NOT NULL DEFAULT 'nuevo',
			PRIMARY KEY  (id),
			KEY idx_fecha (fecha),
			KEY idx_interes (interes(80))
		) {$charset_collate};";

		$sql_postulaciones = "CREATE TABLE {$t_postulaciones} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			fecha datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			nombre varchar(120) NOT NULL,
			cedula varchar(24) NOT NULL,
			correo varchar(120) NOT NULL,
			telefono varchar(40) NOT NULL,
			nivel varchar(80) NOT NULL,
			area varchar(120) NOT NULL,
			resumen text NOT NULL,
			archivo varchar(120) NOT NULL,
			archivo_original varchar(200) NOT NULL DEFAULT '',
			archivo_tamano bigint(20) unsigned NOT NULL DEFAULT 0,
			ip_hash varchar(64) NOT NULL DEFAULT '',
			estado varchar(32) NOT NULL DEFAULT 'nuevo',
			PRIMARY KEY  (id),
			KEY idx_fecha (fecha),
			KEY idx_area (area(80)),
			KEY idx_cedula (cedula)
		) {$charset_collate};";

		dbDelta( $sql_contactos );
		dbDelta( $sql_postulaciones );

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION, false );
	}

	public static function maybe_upgrade(): void {
		if ( get_option( self::DB_VERSION_OPTION ) !== self::DB_VERSION ) {
			self::install();
		}
	}

	/**
	 * Importa la semilla histórica contactos-semilla.csv una única vez si la tabla está vacía.
	 */
	public static function seed_contacts_if_empty(): int {
		global $wpdb;
		$table = self::table_contactos();
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( $count > 0 ) {
			return 0;
		}

		$seed_file = cvg_alcasa_core_data_dir() . '/contactos-semilla.csv';
		if ( ! is_readable( $seed_file ) ) {
			return 0;
		}

		$fp = fopen( $seed_file, 'rb' );
		if ( ! $fp ) {
			return 0;
		}

		$inserted = 0;
		$headers  = array();
		$line_idx = 0;

		while ( ( $row = fgetcsv( $fp ) ) !== false ) {
			if ( 0 === $line_idx ) {
				foreach ( $row as $h ) {
					$h_clean   = preg_replace( '/^\xEF\xBB\xBF/', '', trim( (string) $h ) );
					$headers[] = strtolower( (string) $h_clean );
				}
				$line_idx++;
				continue;
			}
			$line_idx++;

			if ( count( $row ) < 3 ) {
				continue;
			}

			$item = array();
			foreach ( $headers as $i => $key ) {
				$item[ $key ] = trim( (string) ( $row[ $i ] ?? '' ) );
			}

			$nombre = mb_substr( sanitize_text_field( $item['nombre'] ?? '' ), 0, 120 );
			$correo = sanitize_email( $item['correo'] ?? '' );
			if ( '' === $nombre || ! is_email( $correo ) ) {
				continue;
			}

			$fecha_raw = trim( (string) ( $item['fecha'] ?? '' ) );
			$ts        = $fecha_raw ? strtotime( $fecha_raw ) : false;
			$fecha_sql = $ts ? gmdate( 'Y-m-d H:i:s', $ts ) : current_time( 'mysql' );

			$ok = $wpdb->insert(
				$table,
				array(
					'fecha'      => $fecha_sql,
					'nombre'     => $nombre,
					'correo'     => $correo,
					'empresa'    => mb_substr( sanitize_text_field( $item['empresa'] ?? '' ), 0, 120 ),
					'interes'    => mb_substr( sanitize_text_field( $item['interes'] ?? '' ), 0, 160 ),
					'comentario' => mb_substr( sanitize_textarea_field( $item['comentario'] ?? '' ), 0, 2000 ),
					'ip_hash'    => 'semilla',
					'estado'     => 'importado',
				),
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
			);

			if ( $ok ) {
				$inserted++;
			}
		}

		fclose( $fp );
		return $inserted;
	}

	public static function insert_contacto( array $data ): int {
		global $wpdb;
		$ok = $wpdb->insert(
			self::table_contactos(),
			array(
				'fecha'      => current_time( 'mysql' ),
				'nombre'     => mb_substr( $data['nombre'], 0, 120 ),
				'correo'     => mb_substr( $data['correo'], 0, 120 ),
				'empresa'    => mb_substr( $data['empresa'], 0, 120 ),
				'interes'    => mb_substr( $data['interes'], 0, 160 ),
				'comentario' => mb_substr( $data['comentario'], 0, 2000 ),
				'ip_hash'    => $data['ip_hash'],
				'estado'     => 'nuevo',
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		return $ok ? (int) $wpdb->insert_id : 0;
	}

	public static function insert_postulacion( array $data ): int {
		global $wpdb;
		$ok = $wpdb->insert(
			self::table_postulaciones(),
			array(
				'fecha'            => current_time( 'mysql' ),
				'nombre'           => mb_substr( $data['nombre'], 0, 120 ),
				'cedula'           => mb_substr( $data['cedula'], 0, 24 ),
				'correo'           => mb_substr( $data['correo'], 0, 120 ),
				'telefono'         => mb_substr( $data['telefono'], 0, 40 ),
				'nivel'            => mb_substr( $data['nivel'], 0, 80 ),
				'area'             => mb_substr( $data['area'], 0, 120 ),
				'resumen'          => mb_substr( $data['resumen'], 0, 1200 ),
				'archivo'          => mb_substr( $data['archivo'], 0, 120 ),
				'archivo_original' => mb_substr( $data['archivo_original'], 0, 200 ),
				'archivo_tamano'   => (int) $data['archivo_tamano'],
				'ip_hash'          => $data['ip_hash'],
				'estado'           => 'nuevo',
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
		);
		return $ok ? (int) $wpdb->insert_id : 0;
	}

	public static function list_contactos( string $search = '', string $interes = '', int $limit = 200 ): array {
		global $wpdb;
		$table  = self::table_contactos();
		$where  = array( '1=1' );
		$params = array();

		if ( '' !== $interes ) {
			$where[]  = 'interes = %s';
			$params[] = $interes;
		}
		if ( '' !== $search ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '(nombre LIKE %s OR correo LIKE %s OR empresa LIKE %s OR interes LIKE %s OR comentario LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$limit    = max( 1, min( 1000, $limit ) );
		$params[] = $limit;
		$sql      = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY fecha DESC, id DESC LIMIT %d';

		return (array) $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	public static function list_postulaciones( string $search = '', string $area = '', int $limit = 200 ): array {
		global $wpdb;
		$table  = self::table_postulaciones();
		$where  = array( '1=1' );
		$params = array();

		if ( '' !== $area ) {
			$where[]  = 'area = %s';
			$params[] = $area;
		}
		if ( '' !== $search ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '(nombre LIKE %s OR cedula LIKE %s OR correo LIKE %s OR telefono LIKE %s OR nivel LIKE %s OR area LIKE %s OR resumen LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$limit    = max( 1, min( 1000, $limit ) );
		$params[] = $limit;
		$sql      = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY fecha DESC, id DESC LIMIT %d';

		return (array) $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	public static function get_postulacion_by_id( int $id ): ?array {
		global $wpdb;
		$table = self::table_postulaciones();
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
	}

	public static function delete_contacto( int $id ): bool {
		global $wpdb;
		return (bool) $wpdb->delete( self::table_contactos(), array( 'id' => $id ), array( '%d' ) );
	}

	public static function delete_postulacion( int $id ): bool {
		global $wpdb;
		$row = self::get_postulacion_by_id( $id );
		$ok  = (bool) $wpdb->delete( self::table_postulaciones(), array( 'id' => $id ), array( '%d' ) );
		if ( $ok && $row && ! empty( $row['archivo'] ) ) {
			CVG_Alcasa_CV_Storage::delete_file( (string) $row['archivo'] );
		}
		return $ok;
	}

	public static function counts(): array {
		global $wpdb;
		$t_c = self::table_contactos();
		$t_p = self::table_postulaciones();
		return array(
			'contactos'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t_c}" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			'postulaciones' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t_p}" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}
}
