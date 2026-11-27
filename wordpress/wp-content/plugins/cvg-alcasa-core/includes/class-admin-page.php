<?php
/**
 * Panel unificado en wp-admin para gestionar Contactos Comerciales, Postulaciones (CV)
 * y diagnóstico del portal (MySQL, almacenamiento privado y conectividad :5001).
 *
 * Protegido exclusivamente por la capacidad nativa `manage_options` + nonces de WordPress.
 *
 * @package CVG_Alcasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CVG_Alcasa_Admin_Page {

	const MENU_SLUG = 'cvg-alcasa-bandejas';

	public static function register(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_post_cvg_alcasa_download_cv', array( __CLASS__, 'handle_download_cv' ) );
		add_action( 'admin_post_cvg_alcasa_export_csv', array( __CLASS__, 'handle_export_csv' ) );
		add_action( 'admin_post_cvg_alcasa_delete_item', array( __CLASS__, 'handle_delete_item' ) );
	}

	public static function add_menu(): void {
		add_menu_page(
			'CVG Alcasa — Bandejas',
			'CVG Alcasa',
			'manage_options',
			self::MENU_SLUG,
			array( __CLASS__, 'render_page' ),
			'dashicons-building',
			26
		);
	}

	/**
	 * Descarga segura de CV únicamente para administradores autenticados.
	 */
	public static function handle_download_cv(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tienes permisos para descargar currículos.', 'cvg-alcasa-core' ), 403 );
		}

		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		check_admin_referer( 'cvg_alcasa_dl_cv_' . $id );

		$row = CVG_Alcasa_DB::get_postulacion_by_id( $id );
		if ( ! $row || empty( $row['archivo'] ) ) {
			wp_die( esc_html__( 'Postulación o archivo no encontrado.', 'cvg-alcasa-core' ), 404 );
		}

		$path = CVG_Alcasa_CV_Storage::resolve_safe_path( (string) $row['archivo'] );
		if ( null === $path ) {
			wp_die( esc_html__( 'Archivo CV no disponible en el almacenamiento privado.', 'cvg-alcasa-core' ), 404 );
		}

		$download_name = sanitize_file_name( (string) ( $row['archivo_original'] ?: $row['archivo'] ) );
		$ext           = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		$mime_map      = array(
			'pdf'  => 'application/pdf',
			'doc'  => 'application/msword',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		);
		$mime = $mime_map[ $ext ] ?? 'application/octet-stream';

		nocache_headers();
		header( 'Content-Type: ' . $mime );
		header( 'Content-Disposition: attachment; filename="' . $download_name . '"' );
		header( 'Content-Length: ' . (string) filesize( $path ) );
		header( 'X-Content-Type-Options: nosniff' );

		readfile( $path );
		exit;
	}

	/**
	 * Exportación CSV segura (con protección anti-formula injection =+-@).
	 */
	public static function handle_export_csv(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Acceso denegado.', 'cvg-alcasa-core' ), 403 );
		}

		$tipo = isset( $_GET['tipo'] ) ? sanitize_key( wp_unslash( $_GET['tipo'] ) ) : 'contactos';
		check_admin_referer( 'cvg_alcasa_export_' . $tipo );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="cvg-alcasa-' . $tipo . '-' . gmdate( 'Ymd' ) . '.csv"' );

		$out = fopen( 'php://output', 'wb' );
		fwrite( $out, "\xEF\xBB\xBF" );

		if ( 'postulaciones' === $tipo ) {
			fputcsv( $out, array( 'ID', 'Fecha', 'Nombre', 'Cedula', 'Correo', 'Telefono', 'Nivel', 'Area', 'Resumen', 'Archivo' ) );
			foreach ( CVG_Alcasa_DB::list_postulaciones( '', '', 1000 ) as $r ) {
				fputcsv(
					$out,
					array(
						$r['id'],
						cvg_alcasa_core_csv_safe( (string) $r['fecha'] ),
						cvg_alcasa_core_csv_safe( (string) $r['nombre'] ),
						cvg_alcasa_core_csv_safe( (string) $r['cedula'] ),
						cvg_alcasa_core_csv_safe( (string) $r['correo'] ),
						cvg_alcasa_core_csv_safe( (string) $r['telefono'] ),
						cvg_alcasa_core_csv_safe( (string) $r['nivel'] ),
						cvg_alcasa_core_csv_safe( (string) $r['area'] ),
						cvg_alcasa_core_csv_safe( (string) $r['resumen'] ),
						cvg_alcasa_core_csv_safe( (string) $r['archivo'] ),
					)
				);
			}
		} else {
			fputcsv( $out, array( 'ID', 'Fecha', 'Nombre', 'Correo', 'Empresa', 'Interes', 'Comentario' ) );
			foreach ( CVG_Alcasa_DB::list_contactos( '', '', 1000 ) as $r ) {
				fputcsv(
					$out,
					array(
						$r['id'],
						cvg_alcasa_core_csv_safe( (string) $r['fecha'] ),
						cvg_alcasa_core_csv_safe( (string) $r['nombre'] ),
						cvg_alcasa_core_csv_safe( (string) $r['correo'] ),
						cvg_alcasa_core_csv_safe( (string) $r['empresa'] ),
						cvg_alcasa_core_csv_safe( (string) $r['interes'] ),
						cvg_alcasa_core_csv_safe( (string) $r['comentario'] ),
					)
				);
			}
		}

		fclose( $out );
		exit;
	}

	/**
	 * Eliminación autenticada de registro individual.
	 */
	public static function handle_delete_item(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Acceso denegado.', 'cvg-alcasa-core' ), 403 );
		}

		$tipo = isset( $_GET['tipo'] ) ? sanitize_key( wp_unslash( $_GET['tipo'] ) ) : 'contactos';
		$id   = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		check_admin_referer( 'cvg_alcasa_del_' . $tipo . '_' . $id );

		if ( 'postulaciones' === $tipo ) {
			CVG_Alcasa_DB::delete_postulacion( $id );
		} else {
			CVG_Alcasa_DB::delete_contacto( $id );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => self::MENU_SLUG,
					'tab'     => $tipo,
					'deleted' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab    = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'contactos';
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$counts = CVG_Alcasa_DB::counts();
		$base   = admin_url( 'admin.php?page=' . self::MENU_SLUG );
		?>
		<div class="wrap">
			<h1>CVG Alcasa — Panel Unificado de Contactos y Postulaciones (CV)</h1>

			<?php if ( ! empty( $_GET['deleted'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p>Registro eliminado correctamente.</p></div>
			<?php endif; ?>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'contactos', $base ) ); ?>" class="nav-tab <?php echo 'contactos' === $tab ? 'nav-tab-active' : ''; ?>">
					Contactos Comerciales (<?php echo esc_html( (string) $counts['contactos'] ); ?>)
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'postulaciones', $base ) ); ?>" class="nav-tab <?php echo 'postulaciones' === $tab ? 'nav-tab-active' : ''; ?>">
					Postulaciones de CV (<?php echo esc_html( (string) $counts['postulaciones'] ); ?>)
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'sistema', $base ) ); ?>" class="nav-tab <?php echo 'sistema' === $tab ? 'nav-tab-active' : ''; ?>">
					Estado del Sistema y Red (:5001)
				</a>
			</h2>

			<?php if ( 'sistema' === $tab ) : ?>
				<?php
				$storage = CVG_Alcasa_CV_Storage::health_status();
				$news    = CVG_Alcasa_News_Proxy::check_connectivity();
				?>
				<div class="card" style="max-width: 820px; margin-top: 18px;">
					<h2>Diagnóstico Operativo (WordPress / Azure)</h2>
					<table class="widefat striped">
						<tbody>
							<tr>
								<th>Plugin CVG Alcasa Core</th>
								<td>v<?php echo esc_html( CVG_ALCASA_CORE_VERSION ); ?> · PHP <?php echo esc_html( PHP_VERSION ); ?></td>
							</tr>
							<tr>
								<th>Base de datos MySQL ($wpdb)</th>
								<td>Activa · <?php echo esc_html( (string) $counts['contactos'] ); ?> contactos · <?php echo esc_html( (string) $counts['postulaciones'] ); ?> postulaciones</td>
							</tr>
							<tr>
								<th>Almacenamiento privado de CVs</th>
								<td>
									<?php echo $storage['writable'] ? 'Escribible' : 'Sin permisos de escritura'; ?>
									(modo: <code><?php echo esc_html( $storage['storage_mode'] ); ?></code>)
								</td>
							</tr>
							<tr>
								<th>Servidor de Noticias (:5001)</th>
								<td>
									<strong><?php echo $news['reachable'] ? 'CONECTADO (En vivo)' : 'NO ALCANZABLE (Respaldo noticias.json activo)'; ?></strong><br />
									URL: <code><?php echo esc_html( $news['url'] ); ?></code> · HTTP <?php echo esc_html( (string) $news['status'] ); ?> · <?php echo esc_html( (string) $news['latency_ms'] ); ?> ms<br />
									<em><?php echo esc_html( $news['detail'] ); ?></em>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

			<?php elseif ( 'postulaciones' === $tab ) : ?>
				<?php
				$rows       = CVG_Alcasa_DB::list_postulaciones( $search );
				$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=cvg_alcasa_export_csv&tipo=postulaciones' ), 'cvg_alcasa_export_postulaciones' );
				?>
				<form method="get" style="margin: 16px 0; display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
					<input type="hidden" name="page" value="<?php echo esc_attr( self::MENU_SLUG ); ?>" />
					<input type="hidden" name="tab" value="postulaciones" />
					<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Buscar por nombre, cédula, área..." class="regular-text" />
					<button type="submit" class="button">Filtrar</button>
					<a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary">Exportar CSV</a>
				</form>

				<table class="widefat striped">
					<thead>
						<tr>
							<th>Fecha</th>
							<th>Postulante</th>
							<th>Contacto</th>
							<th>Nivel y Área</th>
							<th>Resumen</th>
							<th>Acciones</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $rows ) ) : ?>
							<tr><td colspan="6">No hay postulaciones registradas.</td></tr>
						<?php else : ?>
							<?php foreach ( $rows as $r ) : ?>
								<?php
								$dl_url  = wp_nonce_url( admin_url( 'admin-post.php?action=cvg_alcasa_download_cv&id=' . (int) $r['id'] ), 'cvg_alcasa_dl_cv_' . (int) $r['id'] );
								$del_url = wp_nonce_url( admin_url( 'admin-post.php?action=cvg_alcasa_delete_item&tipo=postulaciones&id=' . (int) $r['id'] ), 'cvg_alcasa_del_postulaciones_' . (int) $r['id'] );
								?>
								<tr>
									<td><?php echo esc_html( (string) $r['fecha'] ); ?></td>
									<td><strong><?php echo esc_html( (string) $r['nombre'] ); ?></strong><br /><small>C.I.: <?php echo esc_html( (string) $r['cedula'] ); ?></small></td>
									<td><a href="mailto:<?php echo esc_attr( (string) $r['correo'] ); ?>"><?php echo esc_html( (string) $r['correo'] ); ?></a><br /><?php echo esc_html( (string) $r['telefono'] ); ?></td>
									<td><strong><?php echo esc_html( (string) $r['area'] ); ?></strong><br /><small><?php echo esc_html( (string) $r['nivel'] ); ?></small></td>
									<td><?php echo esc_html( wp_trim_words( (string) $r['resumen'], 20 ) ); ?></td>
									<td>
										<a href="<?php echo esc_url( $dl_url ); ?>" class="button button-small button-primary">Descargar CV</a>
										<a href="<?php echo esc_url( $del_url ); ?>" class="button button-small" onclick="return confirm('¿Eliminar esta postulación y su archivo CV?');">Eliminar</a>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

			<?php else : ?>
				<?php
				$rows       = CVG_Alcasa_DB::list_contactos( $search );
				$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=cvg_alcasa_export_csv&tipo=contactos' ), 'cvg_alcasa_export_contactos' );
				?>
				<form method="get" style="margin: 16px 0; display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
					<input type="hidden" name="page" value="<?php echo esc_attr( self::MENU_SLUG ); ?>" />
					<input type="hidden" name="tab" value="contactos" />
					<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Buscar por nombre, correo, empresa, producto..." class="regular-text" />
					<button type="submit" class="button">Filtrar</button>
					<a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary">Exportar CSV</a>
				</form>

				<table class="widefat striped">
					<thead>
						<tr>
							<th>Fecha</th>
							<th>Nombre y Empresa</th>
							<th>Correo</th>
							<th>Interés / Producto</th>
							<th>Comentario</th>
							<th>Acciones</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $rows ) ) : ?>
							<tr><td colspan="6">No hay contactos comerciales registrados.</td></tr>
						<?php else : ?>
							<?php foreach ( $rows as $r ) : ?>
								<?php
								$del_url = wp_nonce_url( admin_url( 'admin-post.php?action=cvg_alcasa_delete_item&tipo=contactos&id=' . (int) $r['id'] ), 'cvg_alcasa_del_contactos_' . (int) $r['id'] );
								?>
								<tr>
									<td><?php echo esc_html( (string) $r['fecha'] ); ?></td>
									<td><strong><?php echo esc_html( (string) $r['nombre'] ); ?></strong><br /><small><?php echo esc_html( (string) $r['empresa'] ); ?></small></td>
									<td><a href="mailto:<?php echo esc_attr( (string) $r['correo'] ); ?>"><?php echo esc_html( (string) $r['correo'] ); ?></a></td>
									<td><?php echo esc_html( (string) $r['interes'] ); ?></td>
									<td><?php echo esc_html( wp_trim_words( (string) $r['comentario'], 24 ) ); ?></td>
									<td>
										<a href="<?php echo esc_url( $del_url ); ?>" class="button button-small" onclick="return confirm('¿Eliminar este registro de contacto?');">Eliminar</a>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}
}
