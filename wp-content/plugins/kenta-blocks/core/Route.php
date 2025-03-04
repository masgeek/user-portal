<?php
/**
 * Api route
 *
 * @package Kenta Blocks
 */

namespace KentaBlocks;

use KentaBlocks\API\Router;

class Route {

	/**
	 * Register api v1
	 */
	public static function api_v1() {
		$router = new Router( 'kenta-blocks/v1' );

		// Settings api
		$router->read( '/settings', array( Route::class, 'fetch' ) )->use( array(
			Route::class,
			'can_manage_options'
		) );
		$router->create( '/settings', array( Route::class, 'save' ) )->use( array(
			Route::class,
			'can_manage_options'
		) );
		$router->create( '/settings/assets', array( Route::class, 'regenerate' ) )->use( array(
			Route::class,
			'can_manage_options'
		) );

		/**
		 *  Library api
		 */

		// Clear cache
		$router->edit( '/library/cache', array( Route::class, 'clear' ) )->use( array(
			Route::class,
			'can_edit_posts'
		) );
		// Get all pattern categories
		$router->read( '/library/categories', array( Route::class, 'categories' ) )->use( array(
			Route::class,
			'can_edit_posts'
		) );
		// Get all patterns
		$router->read( '/library/patterns', array( Route::class, 'patterns' ) )->use( array(
			Route::class,
			'can_edit_posts'
		) );
		// Get pattern content
		$router->read( '/library/patterns/(?P<id>[\d]+)', array( Route::class, 'pattern' ) )->use( array(
			Route::class,
			'can_edit_posts'
		) );

		$router->register();
	}

	/**
	 * API Auth
	 *
	 * @param $request
	 * @param $next
	 *
	 * @return mixed|\WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public static function can_manage_options( $request, $next ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return rest_ensure_response( new \WP_Error( 403, __( 'Forbidden', 'kenta-blocks' ) ) );
		}

		return $next( $request );
	}

	/**
	 * API Auth
	 *
	 * @param $request
	 * @param $next
	 *
	 * @return mixed|\WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public static function can_edit_posts( $request, $next ) {

		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return rest_ensure_response( new \WP_Error( 403, __( 'Forbidden', 'kenta-blocks' ) ) );
		}

		return $next( $request );
	}

	/**
	 * Fetch all settings
	 */
	public static function fetch() {
		return rest_ensure_response( array(
			'template' => kenta_blocks_current_template(),
			'settings' => kenta_blocks_setting()->values()
		) );
	}

	/**
	 * Save all settings
	 */
	public static function save( $request ) {
		$settings = $request->get_param( 'settings' );
		foreach ( $settings as $id => $value ) {
			kenta_blocks_setting()->save( $id, $value );
		}

		return rest_ensure_response( $settings );
	}

	/**
	 * Regenerate dynamic assets file
	 */
	public static function regenerate() {
		kenta_blocks_regenerate_assets();

		return rest_ensure_response( array( 'status' => 'ok' ) );
	}

	/**
	 * Remove all library patterns cache
	 *
	 * @param $request
	 *
	 * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public static function clear( $request ) {
		delete_transient( 'kb-library-categories' );
		delete_transient( 'kb-library-categories-version' );
		delete_transient( 'kb-library-patterns' );
		delete_transient( 'kb-library-patterns-version' );

		return rest_ensure_response( array( 'status' => 'ok' ) );
	}

	/**
	 * Get library categories
	 */
	public static function categories( $request ) {
		$categories    = get_transient( 'kb-library-categories' );
		$cache_version = get_transient( 'kb-library-categories-version' );
		// Check from cache first
		if ( false === $categories || $cache_version !== KENTA_BLOCKS_VERSION ) {
			$categories = self::do_library_api_request( '/categories' );
			if ( ! is_wp_error( $categories ) ) {
				set_transient( 'kb-library-categories', $categories, KENTA_BLOCKS_LIBRARY_API_CACHE_SECONDS );
				set_transient( 'kb-library-categories-version', KENTA_BLOCKS_VERSION, KENTA_BLOCKS_LIBRARY_API_CACHE_SECONDS );
			}
		}

		return rest_ensure_response( $categories );
	}

	/**
	 * Do library api request
	 *
	 * @param $endpoint
	 *
	 * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	private static function do_library_api_request( $endpoint ) {
		$url      = KENTA_BLOCKS_LIBRARY_API . $endpoint;
		$response = wp_remote_get( $url, array(
			'timeout' => apply_filters( 'kb/timeout_for_library_api_request', 20 )
		) );

		// Test if the get request was not successful.
		if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ) {
			$response_error = self::get_error_from_response( $response );

			return new \WP_Error(
				'http_error',
				sprintf( /* translators: %1$s and %3$s - strong HTML tags, %2$s - file URL, %4$s - br HTML tag, %5$s - error code, %6$s - error message. */
					__( 'An error occurred: %1$s - %2$s.', 'kenta-blocks' ),
					$response_error['error_code'],
					$response_error['error_message']
				) .
				apply_filters( 'kb/message_after_api_request_error', '' )
			);
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Helper function: get the right format of response errors.
	 *
	 * @param array|\WP_Error $response Array or WP_Error or the response.
	 *
	 * @return array Error code and error message.
	 */
	private static function get_error_from_response( $response ) {
		$response_error = array();

		if ( is_array( $response ) ) {
			$response_error['error_code']    = $response['response']['code'];
			$response_error['error_message'] = $response['response']['message'];
		} else {
			$response_error['error_code']    = $response->get_error_code();
			$response_error['error_message'] = $response->get_error_message();
		}

		return $response_error;
	}

	/**
	 * Get patterns list
	 *
	 * @param $request
	 *
	 * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public static function patterns( $request ) {
		$paged        = max( absint( $request->get_param( 'paged' ) ), 1 );
		$taxonomy     = $request->get_param( 'taxonomy' );
		$cache_key    = "kb-patterns-{$paged}-{$taxonomy}";
		$all_patterns = get_transient( 'kb-library-patterns' ) ?? array();
		$taxonomy     = $taxonomy ? "&taxonomy={$taxonomy}" : '';

		// Reset patterns cache after plugin update
		if ( get_transient( 'kb-library-patterns-version' ) !== KENTA_BLOCKS_VERSION ) {
			$all_patterns = array();
			set_transient( 'kb-library-patterns-version', KENTA_BLOCKS_VERSION, KENTA_BLOCKS_LIBRARY_API_CACHE_SECONDS );
		}

		// Check from cache first
		if ( ! isset( $all_patterns[ $cache_key ] ) ) {
			$endpoint = "/patterns&paged={$paged}{$taxonomy}";
			$patterns = self::do_library_api_request( $endpoint );
			if ( ! is_wp_error( $patterns ) ) {
				$all_patterns[ $cache_key ] = $patterns;
				set_transient( 'kb-library-patterns', $all_patterns, KENTA_BLOCKS_LIBRARY_API_CACHE_SECONDS );
			}
		}

		return rest_ensure_response( $all_patterns[ $cache_key ] );
	}

	/**
	 * Get pattern content
	 *
	 * @param $request
	 *
	 * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public static function pattern( $request ) {
		$id       = absint( $request->get_param( 'id' ) );
		$license  = kb_fs()->_get_license();
		$endpoint = "/patterns/{$id}";

		if ( $license ) {
			$license  = kenta_blocks_urlsafe_b64encode( $license->secret_key );
			$endpoint .= "&license={$license}";
		}

		$result = self::do_library_api_request( $endpoint );
		if ( is_array( $result ) && isset( $result['content'] ) ) {

			if ( isset( $result['min_version'] ) && $result['min_version'] !== '' && version_compare( KENTA_BLOCKS_VERSION, $result['min_version'], '<' ) ) {
				return rest_ensure_response(
					new \WP_Error( '403', __( 'Your plugin version is too old, please upgrade to the latest version and try again.', 'kenta-blocks' ) )
				);
			}

			$result['content'] = wp_kses_post( kenta_blocks_process_import_content_urls( $result['content'] ) );
		}

		return $result;
	}
}
