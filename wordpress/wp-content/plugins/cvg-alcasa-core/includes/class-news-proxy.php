<?php
/**
 * Proxy resiliente de noticias hacia http://alcasa.com.ve:5001 usando wp_remote_get(),
 * WordPress Transients (TTL 120s) y respaldo automático en data/noticias.json.
 *
 * @package CVG_Alcasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CVG_Alcasa_News_Proxy {

	const CACHE_TTL = 120;

	public static function base_url(): string {
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

	private static function http_get( string $url, int $timeout = 6 ): string {
		$resp = wp_remote_get(
			$url,
			array(
				'timeout'     => $timeout,
				'redirection' => 3,
				'user-agent'  => 'CVG-Alcasa-WordPress/' . CVG_ALCASA_CORE_VERSION,
			)
		);
		if ( is_wp_error( $resp ) ) {
			return '';
		}
		if ( 200 !== (int) wp_remote_retrieve_response_code( $resp ) ) {
			return '';
		}
		return (string) wp_remote_retrieve_body( $resp );
	}

	private static function abs_portal_url( string $u ): string {
		$u = trim( $u );
		if ( '' === $u ) {
			return '';
		}
		if ( preg_match( '/^https?:\/\//i', $u ) || 0 === strpos( $u, '/assets/' ) || 0 === strpos( $u, 'data:' ) ) {
			return $u;
		}
		return ( '/' === $u[0] ) ? ( self::base_url() . $u ) : ( self::base_url() . '/' . $u );
	}

	/**
	 * Lee el respaldo local data/noticias.json.
	 */
	public static function read_fallback_list(): array {
		$file = cvg_alcasa_core_data_dir() . '/noticias.json';
		if ( ! is_readable( $file ) ) {
			return array();
		}
		$raw = file_get_contents( $file );
		if ( false === $raw ) {
			return array();
		}
		$parsed = json_decode( $raw, true );
		if ( ! is_array( $parsed ) ) {
			return array();
		}
		$items = isset( $parsed['noticias'] ) && is_array( $parsed['noticias'] ) ? $parsed['noticias'] : $parsed;
		return is_array( $items ) ? array_values( $items ) : array();
	}

	private static function fallback_map(): array {
		$map = array();
		foreach ( self::read_fallback_list() as $item ) {
			if ( is_array( $item ) && isset( $item['id'] ) ) {
				$map[ (string) $item['id'] ] = $item;
			}
		}
		return $map;
	}

	/**
	 * Obtiene el detalle de una noticia individual (?id=N).
	 */
	public static function get_detail( string $id, bool $no_cache = false ): ?array {
		$clean_id = preg_replace( '/[^a-zA-Z0-9_-]/', '', $id );
		if ( '' === $clean_id ) {
			return null;
		}

		$t_key = 'alcasa_news_id_' . md5( $clean_id );
		if ( ! $no_cache ) {
			$cached = get_transient( $t_key );
			if ( is_array( $cached ) ) {
				return $cached;
			}
		}

		$base = self::base_url();
		$html = self::http_get( $base . '/noticia/' . rawurlencode( $clean_id ), 6 );

		if ( '' !== $html && preg_match( '/<article class="detalle-noticia">(.*?)<\/article>/s', $html, $am ) ) {
			$art = $am[1];

			$tit = preg_match( '/<h1[^>]*>(.*?)<\/h1>/s', $art, $m ) ? trim( html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) : ( 'Comunicado #' . $clean_id );
			$sub = preg_match( '/<h2 class="subtitulo-detalle"[^>]*>(.*?)<\/h2>/s', $art, $m ) ? trim( html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) : '';
			$aut = preg_match( '/👤\s*([^<]+)/u', $art, $m ) ? trim( html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) : 'Comunicación Estratégica';
			$fec = preg_match( '/📅\s*([0-9\/]+\s*[0-9:]*)/u', $art, $m ) ? trim( $m[1] ) : '';
			$cat = preg_match( '/<span class="categoria">(.*?)<\/span>/s', $art, $m ) ? trim( html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) : 'Institucional';

			$img = '';
			if ( preg_match( '/<div class="imagen-principal">.*?<img\s+src="([^"]+)"/s', $art, $m ) ) {
				$img = self::abs_portal_url( $m[1] );
			}

			$nom_archivo = '';
			if ( preg_match( '/<div class="nombre-archivo"[^>]*>(.*?)<\/div>/s', $art, $m ) ) {
				$nom_archivo = trim( str_replace( '📎', '', html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
			}

			$cuerpo = '';
			if ( preg_match( '/<div class="texto-principal">(.*?)<\/div>\s*<\/div>\s*<div class="acciones-detalle">/s', $art, $m )
				|| preg_match( '/<div class="texto-principal">(.*?)<\/div>/s', $art, $m ) ) {
				$raw_body = trim( $m[1] );
				$raw_body = (string) preg_replace_callback(
					'/\b(src|href)="(\/[^"]+)"/i',
					static function ( array $mm ) use ( $base ): string {
						return $mm[1] . '="' . esc_url( $base . $mm[2] ) . '"';
					},
					$raw_body
				);
				$cuerpo = wp_kses_post( $raw_body );
			}

			$result = array(
				'id'            => $clean_id,
				'esVivo'        => true,
				'url'           => $base . '/noticia/' . rawurlencode( $clean_id ),
				'titulo'        => cvg_alcasa_core_normalize_brand( $tit ),
				'subtitulo'     => cvg_alcasa_core_normalize_brand( $sub ),
				'categoria'     => cvg_alcasa_core_normalize_brand( $cat ),
				'autor'         => $aut,
				'fecha'         => $fec,
				'imagen'        => $img,
				'nombreArchivo' => $nom_archivo,
				'cuerpo'        => $cuerpo,
			);
			set_transient( $t_key, $result, self::CACHE_TTL );
			return $result;
		}

		// Respaldo local en data/noticias.json
		$bk_map = self::fallback_map();
		if ( isset( $bk_map[ $clean_id ] ) ) {
			$bk      = $bk_map[ $clean_id ];
			$imgs    = ( ! empty( $bk['imagenes'] ) && is_array( $bk['imagenes'] ) ) ? $bk['imagenes'] : array( array( 'url' => $bk['imagen'] ?? '' ) );
			$img_url = (string) ( $imgs[0]['url'] ?? ( $bk['imagen'] ?? '/assets/img/noticias/noticia-761.jpg' ) );
			$cuerpo  = (string) ( $bk['cuerpo'] ?? ( $bk['resumen'] ?? '' ) );
			$html_b  = ( false !== strpos( $cuerpo, '<p' ) || false !== strpos( $cuerpo, '<div' ) )
				? wp_kses_post( $cuerpo )
				: '<p>' . nl2br( esc_html( $cuerpo ) ) . '</p>';

			return array(
				'id'            => $clean_id,
				'esVivo'        => false,
				'url'           => $base . '/noticia/' . rawurlencode( $clean_id ),
				'titulo'        => cvg_alcasa_core_normalize_brand( (string) ( $bk['titulo'] ?? 'Comunicado' ) ),
				'subtitulo'     => cvg_alcasa_core_normalize_brand( (string) ( $bk['subtitulo'] ?? '' ) ),
				'categoria'     => cvg_alcasa_core_normalize_brand( (string) ( $bk['categoria'] ?? 'Institucional' ) ),
				'autor'         => (string) ( $bk['autor'] ?? 'Comunicación Estratégica' ),
				'fecha'         => trim( (string) ( $bk['fecha_solo'] ?? ( $bk['fecha'] ?? '' ) ) . ' ' . (string) ( $bk['hora_solo'] ?? '' ) ),
				'imagen'        => $img_url,
				'nombreArchivo' => basename( $img_url ),
				'cuerpo'        => $html_b,
			);
		}

		return null;
	}

	/**
	 * Obtiene listado paginado/filtrado de noticias (?page, ?q, ?fecha, ?mes, ?anio) con fallback.
	 */
	public static function get_list( array $args = array() ): array {
		$page     = isset( $args['page'] ) ? max( 0, (int) $args['page'] ) : 0;
		$q        = trim( (string) ( $args['q'] ?? '' ) );
		$fecha    = trim( (string) ( $args['fecha'] ?? '' ) );
		$mes      = trim( (string) ( $args['mes'] ?? '' ) );
		$anio     = trim( (string) ( $args['anio'] ?? '' ) );
		$no_cache = ! empty( $args['nocache'] );

		$has_filters = ( '' !== $q || '' !== $fecha || '' !== $mes || '' !== $anio );
		$cache_key   = 'alcasa_news_l_' . md5( $page . '|' . $q . '|' . $fecha . '|' . $mes . '|' . $anio );

		if ( ! $no_cache ) {
			$cached = get_transient( $cache_key );
			if ( is_array( $cached ) ) {
				return $cached;
			}
		}

		$eff_page = $page > 0 ? $page : 1;
		$query    = array( 'page' => $eff_page );
		if ( '' !== $q ) {
			$query['q'] = $q;
		}
		if ( '' !== $fecha ) {
			$query['fecha'] = $fecha;
		}
		if ( '' !== $mes ) {
			$query['mes'] = $mes;
		}
		if ( '' !== $anio ) {
			$query['anio'] = $anio;
		}

		$base      = self::base_url();
		$page_html = self::http_get( $base . '/?' . http_build_query( $query ), 6 );
		$bk_map    = self::fallback_map();

		if ( '' !== $page_html && preg_match_all( '/<article class="noticia-card">(.*?)<\/article>/s', $page_html, $matches ) ) {
			$total_pages = $has_filters ? 1 : 72;
			if ( preg_match_all( '/[?&]page=(\d+)/', $page_html, $pm ) && ! empty( $pm[1] ) ) {
				$max_p = max( array_map( 'intval', $pm[1] ) );
				if ( $max_p > 0 ) {
					$total_pages = max( $eff_page, $max_p );
				}
			}

			$items = array();
			foreach ( $matches[1] as $card_html ) {
				if ( ! preg_match( '/href="\/noticia\/(\d+)"/', $card_html, $id_m ) ) {
					continue;
				}
				$nid = (string) $id_m[1];
				$bk  = $bk_map[ $nid ] ?? array();

				$titulo = preg_match( '/<h2[^>]*>.*?<a[^>]*>(.*?)<\/a>/s', $card_html, $m ) ? trim( html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) : ( 'Comunicado #' . $nid );
				$sub    = preg_match( '/<div class="subtitulo"[^>]*>(.*?)<\/div>/s', $card_html, $m ) ? trim( html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) : '';
				$cat    = preg_match( '/<span class="categoria"[^>]*>(.*?)<\/span>/s', $card_html, $m ) ? trim( html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) : ( (string) ( $bk['categoria'] ?? 'Institucional' ) );
				$res    = preg_match( '/<p class="resumen"[^>]*>(.*?)<\/p>/s', $card_html, $m ) ? trim( html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) : ( (string) ( $bk['resumen'] ?? '' ) );
				$aut    = preg_match( '/👤\s*([^📅<]+)/u', $card_html, $m ) ? trim( html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) : 'Comunicación Estratégica';

				$fecha_solo = '';
				$hora_solo  = '';
				if ( preg_match( '/(\d{2}\/\d{2}\/\d{4})(?:\s+(\d{2}:\d{2}))?/', $card_html, $fm ) ) {
					$fecha_solo = $fm[1];
					$hora_solo  = $fm[2] ?? '';
				}

				$img = '';
				if ( ! empty( $bk['imagen'] ) && 0 === strpos( (string) $bk['imagen'], '/assets/' ) ) {
					$img = (string) $bk['imagen'];
				} elseif ( preg_match( '/<img[^>]+src="([^"]+)"/i', $card_html, $im ) ) {
					$img = self::abs_portal_url( $im[1] );
				} else {
					$img = '/assets/img/noticias/noticia-761.jpg';
				}

				$items[] = array(
					'id'         => $nid,
					'estado'     => 'publicada',
					'categoria'  => cvg_alcasa_core_normalize_brand( $cat ),
					'subtitulo'  => cvg_alcasa_core_normalize_brand( $sub ),
					'titulo'     => cvg_alcasa_core_normalize_brand( ! empty( $bk['titulo'] ) ? (string) $bk['titulo'] : $titulo ),
					'resumen'    => cvg_alcasa_core_normalize_brand( $res ),
					'imagen'     => $img,
					'imagenes'   => array( array( 'url' => $img, 'pie' => '' ) ),
					'autor'      => $aut,
					'fecha'      => trim( $fecha_solo . ' ' . $hora_solo ),
					'fecha_solo' => $fecha_solo,
					'hora_solo'  => $hora_solo,
					'url'        => $base . '/noticia/' . rawurlencode( $nid ),
				);
			}

			$payload = array(
				'source'      => 'live',
				'page'        => $eff_page,
				'total_pages' => $total_pages,
				'total'       => count( $items ),
				'noticias'    => $items,
			);
			set_transient( $cache_key, $payload, self::CACHE_TTL );
			return $payload;
		}

		// Respaldo automático local (data/noticias.json) cuando :5001 no responde desde Azure o entorno aislado.
		$fallback = self::read_fallback_list();
		if ( $has_filters ) {
			$fallback = array_values(
				array_filter(
					$fallback,
					static function ( $n ) use ( $q, $fecha, $mes, $anio ): bool {
						if ( ! is_array( $n ) ) {
							return false;
						}
						if ( '' !== $q ) {
							$hay = mb_strtolower(
								(string) ( $n['titulo'] ?? '' ) . ' ' . (string) ( $n['subtitulo'] ?? '' ) . ' ' . (string) ( $n['resumen'] ?? '' ) . ' ' . (string) ( $n['categoria'] ?? '' ),
								'UTF-8'
							);
							if ( false === mb_strpos( $hay, mb_strtolower( $q, 'UTF-8' ) ) ) {
								return false;
							}
						}
						$f_solo = (string) ( $n['fecha_solo'] ?? ( $n['fecha'] ?? '' ) );
						if ( '' !== $fecha ) {
							$parts = explode( '-', $fecha );
							$dmy   = ( 3 === count( $parts ) ) ? ( $parts[2] . '/' . $parts[1] . '/' . $parts[0] ) : $fecha;
							if ( false === strpos( $f_solo, $dmy ) ) {
								return false;
							}
						}
						if ( '' !== $mes && false === strpos( $f_solo, '/' . $mes . '/' ) ) {
							return false;
						}
						if ( '' !== $anio && false === strpos( $f_solo, $anio ) ) {
							return false;
						}
						return true;
					}
				)
			);
		}

		return array(
			'source'      => 'fallback',
			'page'        => 1,
			'total_pages' => 1,
			'total'       => count( $fallback ),
			'noticias'    => $fallback,
		);
	}

	/**
	 * Prueba directa de conectividad desde este servidor WordPress hacia alcasa.com.ve:5001.
	 */
	public static function check_connectivity(): array {
		$url   = self::base_url() . '/?page=1';
		$start = microtime( true );
		$resp  = wp_remote_get(
			$url,
			array(
				'timeout'     => 5,
				'redirection' => 2,
			)
		);
		$ms = (int) round( ( microtime( true ) - $start ) * 1000 );

		if ( is_wp_error( $resp ) ) {
			return array(
				'reachable'  => false,
				'url'        => $url,
				'latency_ms' => $ms,
				'status'     => 0,
				'mode'       => 'fallback-local',
				'detail'     => $resp->get_error_message(),
			);
		}

		$code = (int) wp_remote_retrieve_response_code( $resp );
		return array(
			'reachable'  => ( $code >= 200 && $code < 400 ),
			'url'        => $url,
			'latency_ms' => $ms,
			'status'     => $code,
			'mode'       => ( $code >= 200 && $code < 400 ) ? 'live' : 'fallback-local',
			'detail'     => ( $code >= 200 && $code < 400 ) ? 'Conectado al servidor de noticias' : 'Código HTTP no satisfactorio; operando con respaldo local',
		);
	}
}
