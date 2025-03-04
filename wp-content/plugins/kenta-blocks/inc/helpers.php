<?php
/**
 * Helper functions for our plugin
 *
 * @package Kenta Blocks
 */

if ( ! function_exists( 'kenta_blocks_css' ) ) {
	/**
	 * Get css utils instance
	 *
	 * @return \KentaBlocks\Css
	 */
	function kenta_blocks_css() {
		return \KentaBlocks\Css::get_instance();
	}
}

if ( ! function_exists( 'kenta_blocks_script' ) ) {
	/**
	 * Get script utils instance
	 *
	 * @return \KentaBlocks\Script
	 */
	function kenta_blocks_script() {
		return \KentaBlocks\Script::get_instance();
	}
}

if ( ! function_exists( 'kenta_blocks_assets' ) ) {
	/**
	 * Get assets utils instance
	 *
	 * @return \KentaBlocks\Assets
	 */
	function kenta_blocks_assets() {
		return \KentaBlocks\Assets::get_instance();
	}
}

if ( ! function_exists( 'kenta_blocks_setting' ) ) {
	/**
	 * Get settings instance or setting value
	 */
	function kenta_blocks_setting( $key = null ) {
		$instance = \KentaBlocks\Settings::get_instance();
		if ( $key !== null ) {
			return $instance->get( $key );
		}

		return $instance;
	}
}

if ( ! function_exists( 'kenta_blocks_parse_content' ) ) {
	/**
	 * Parse blocks from content
	 *
	 * @param $content
	 *
	 * @return array[]
	 */
	function kenta_blocks_parse_content( $content ) {

		global $wp_version;

		return ( version_compare( $wp_version, '5', '>=' ) ) ? parse_blocks( $content ) : gutenberg_parse_blocks( $content );
	}
}

if ( ! function_exists( 'kenta_blocks_all' ) ) {
	/**
	 * Get all blocks
	 *
	 * @param null $key
	 *
	 * @return array|mixed
	 */
	function kenta_blocks_all( $key = null ) {
		$blocks = \KentaBlocks\Store::get( 'kenta-blocks' . $key, function () {
			return require KENTA_BLOCKS_PLUGIN_PATH . 'inc/blocks.php';
		} );

		if ( $key ) {
			$blocks = array_map( function ( $item ) use ( $key ) {
				return $item[ $key ];
			}, $blocks );
		}

		return $blocks;
	}
}

if ( ! function_exists( 'kenta_blocks_template_part' ) ) {
	/**
	 * Include template
	 *
	 * @param $slug
	 */
	function kenta_blocks_template_part( $slug ) {
		$path = KENTA_BLOCKS_PLUGIN_PATH . 'templates/' . $slug . '.php';
		if ( file_exists( $path ) ) {
			require $path;
		}
	}
}

if ( ! function_exists( 'kenta_blocks_current_template' ) ) {
	/**
	 * Get current template name
	 *
	 * @return string
	 */
	function kenta_blocks_current_template() {
		return strtolower( str_replace( '-premium', '', get_option( 'template' ) ) );
	}
}

if ( ! function_exists( 'kenta_blocks_urlsafe_b64encode' ) ) {
	/**
	 * URL safe base64 encode
	 *
	 * @param $input
	 *
	 * @return array|string|string[]
	 */
	function kenta_blocks_urlsafe_b64encode( $input ) {
		return str_replace( '=', '', strtr( base64_encode( $input ), '+/', '-_' ) );
	}
}

if ( ! function_exists( 'kenta_blocks_is_image_url' ) ) {
	/**
	 * Check if the URL points to an image.
	 *
	 * @param string $url Valid URL.
	 *
	 * @return false|int
	 */
	function kenta_blocks_is_image_url( $url ) {
		return preg_match( '/^((https?:\/\/)|(www\.))([a-z\d-].?)+(:\d+)?\/[\w\-]+\.(jpg|png|gif|jpeg|webp)\/?$/i', $url );
	}
}

if ( ! function_exists( 'kenta_blocks_is_local_image' ) ) {
	/**
	 * Check if the image exists in the system.
	 *
	 * @param $data
	 *
	 * @return array
	 */
	function kenta_blocks_is_local_image( $data ) {
		global $wpdb;

		$image_id = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT `post_id` FROM `' . $wpdb->postmeta . '`
					WHERE `meta_key` = \'_kb_image_hash\'
						AND `meta_value` = %s
				;',
				sha1( $data['url'] )
			)
		);

		if ( $image_id ) {
			$local_image = array(
				'id'  => $image_id,
				'url' => wp_get_attachment_url( $image_id ),
			);

			return array(
				'status' => true,
				'image'  => $local_image,
			);
		}

		return array(
			'status' => false,
			'image'  => $data,
		);
	}
}

if ( ! function_exists( 'kenta_blocks_do_image_import' ) ) {
	/**
	 * Import an external image
	 *
	 * @param $data
	 *
	 * @return array|mixed
	 */
	function kenta_blocks_do_image_import( $data ) {
		$local_image = kenta_blocks_is_local_image( $data );

		if ( $local_image['status'] ) {
			return $local_image['image'];
		}

		$file_content = wp_remote_retrieve_body(
			wp_safe_remote_get(
				$data['url'],
				array(
					'timeout'   => '60',
					'sslverify' => false,
				)
			)
		);

		if ( empty( $file_content ) ) {
			return $data;
		}

		$filename = basename( $data['url'] );

		$upload = wp_upload_bits( $filename, null, $file_content );
		$post   = array(
			'post_title' => $filename,
			'guid'       => $upload['url'],
		);
		$info   = wp_check_filetype( $upload['file'] );

		if ( $info ) {
			$post['post_mime_type'] = $info['type'];
		} else {
			return $data;
		}

		$post_id = wp_insert_attachment( $post, $upload['file'] );

		require_once ABSPATH . 'wp-admin/includes/image.php';

		wp_update_attachment_metadata(
			$post_id,
			wp_generate_attachment_metadata( $post_id, $upload['file'] )
		);

		update_post_meta( $post_id, '_kb_image_hash', sha1( $data['url'] ) );

		return array(
			'id'  => $post_id,
			'url' => $upload['url'],
		);
	}
}

if ( ! function_exists( 'kenta_blocks_process_import_content_urls' ) ) {
	/**
	 * Process urls in import content
	 *
	 * @param string $content
	 *
	 * @return array|mixed|string|string[]
	 */
	function kenta_blocks_process_import_content_urls( $content = '' ) {
		preg_match_all( '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $content, $match );

		$urls = array_unique( $match[0] );

		if ( empty( $urls ) ) {
			return $content;
		}

		$map_urls   = array();
		$image_urls = array();

		foreach ( $urls as $url ) {
			if ( kenta_blocks_is_image_url( $url ) ) {
				$image_urls[] = $url;
			}
		}

		if ( ! empty( $image_urls ) ) {
			foreach ( $image_urls as $image_url ) {
				$image                  = array(
					'url' => $image_url,
					'id'  => 0,
				);
				$downloaded_image       = kenta_blocks_do_image_import( $image );
				$map_urls[ $image_url ] = $downloaded_image['url'];
			}
		}

		foreach ( $map_urls as $old_url => $new_url ) {
			$content = str_replace( $old_url, $new_url, $content );
			$old_url = str_replace( '/', '/\\', $old_url );
			$new_url = str_replace( '/', '/\\', $new_url );
			$content = str_replace( $old_url, $new_url, $content );
		}

		return $content;
	}
}

if ( ! function_exists( 'kenta_blocks_notices' ) ) {
	/**
	 * Get notices instance
	 *
	 * @return mixed|\Wpmoose\WpDismissibleNotice\Notices
	 */
	function kenta_blocks_notices() {
		return \Wpmoose\WpDismissibleNotice\Notices::instance(
			'kenta_blocks',
			KENTA_BLOCKS_PLUGIN_URL . 'vendor/wpmoose/wp-dismissible-notice/'
		);
	}
}

if ( ! function_exists( 'kenta_blocks_regenerate_assets' ) ) {
	/**
	 * Regenerate all assets file when next page loading
	 */
	function kenta_blocks_regenerate_assets() {
		update_option( 'kenta_blocks_dynamic_assets_posts', array() );
	}
}

if ( ! function_exists( 'kb_clsx' ) ) {
	/**
	 * A utility for constructing className strings conditionally.
	 *
	 * @param ...$args
	 *
	 * @return string
	 */
	function kb_clsx( ...$args ) {
		$classNames = array();

		foreach ( $args as $arg ) {
			if ( is_string( $arg ) && $arg !== '' ) {
				$classNames[] = $arg;
			} else if ( is_array( $arg ) ) {
				foreach ( $arg as $k => $v ) {
					if ( is_string( $v ) ) {
						$classNames[] = $v;
					} else if ( is_bool( $v ) && $v === true ) {
						$classNames[] = $k;
					}
				}
			}
		}

		return implode( ' ', $classNames );
	}
}

if ( ! function_exists( 'kb_the_clsx' ) ) {
	/**
	 * Echo version for kb_clsx
	 *
	 * @param ...$args
	 */
	function kb_the_clsx( ...$args ) {
		echo esc_attr( kb_clsx( ...$args ) );
	}
}

if ( ! function_exists( 'kb_layers' ) ) {
	/**
	 * Get available layers from layers control
	 *
	 * @param $layers
	 *
	 * @return array
	 */
	function kb_layers( $layers ) {
		if ( ! is_array( $layers ) ) {
			return [];
		}

		$result = [];

		foreach ( $layers as $layer ) {
			if ( isset( $layer['id'] ) && ( $layer['visible'] === 'true' || $layer['visible'] === true ) ) {
				$result[] = $layer['id'];
			}
		}

		return $result;
	}
}

if ( ! function_exists( 'kb_image' ) ) {
	/**
	 * Get image file
	 *
	 * @param $name
	 *
	 * @return mixed|string
	 */
	function kb_image( $name ) {
		$svgs = [
			'none'      => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32"><path d="M9.943 17.415h-0.065l-2.095-3.199-2.559-3.777h-1.984v11.025h2.191v-6.944h0.095l1.819 2.784 2.793 4.16h1.996v-11.025h-2.191v6.977zM12.904 22.135h1.615l4.049-12.271h-1.633l-4.031 12.271zM24.92 10.439h-2.24l-3.874 11.025h2.336l0.72-2.273h3.841l0.672 2.273h2.384l-3.84-11.025zM22.455 17.352l0.447-1.456 0.85-2.864h0.063l0.866 2.913 0.431 1.408h-2.656z"></path></svg>',
			/**
			 * Divider
			 */
			'divider-1' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path d="M9,17.2l5.1-10.9L15,6.8L9.9,17.6L9,17.2z"/></svg>',
			'divider-2' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path d="M8.9,11.5h6.3v1H8.9V11.5z"/></svg>',
			'divider-3' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20"><path d="M7.8 10c0 1.215 0.986 2.2 2.201 2.2s2.199-0.986 2.199-2.2c0-1.215-0.984-2.199-2.199-2.199s-2.201 0.984-2.201 2.199z"></path></svg>',
		];

		$svgs = apply_filters( 'kb_svg_images', $svgs );

		if ( ! isset( $svgs[ $name ] ) ) {
			return '';
		}

		return $svgs[ $name ];
	}
}

if ( ! function_exists( 'kb_image_url' ) ) {
	/**
	 * Get image file url
	 *
	 * @param $path
	 *
	 * @return string
	 */
	function kb_image_url( $path ): string {
		return KENTA_BLOCKS_PLUGIN_URL . 'assets/images/' . $path;
	}
}

/**
 * WooCommerce helpers
 */

if ( ! function_exists( 'kb_wc_get_cart_item_quantities_by_product_id' ) ) {
	/**
	 * Get the number of items in the cart for a given product id.
	 *
	 * @param $product_id
	 *
	 * @return int|mixed
	 */
	function kb_wc_get_cart_item_quantities_by_product_id( $product_id ) {
		if ( ! isset( \WC()->cart ) ) {
			return 0;
		}

		$cart = \WC()->cart->get_cart_item_quantities();

		return isset( $cart[ $product_id ] ) ? $cart[ $product_id ] : 0;
	}
}

if ( ! function_exists( 'kb_sidebar_blocks' ) ) {
	/**
	 * Get all blocks in the sidebar
	 *
	 * @return array|array[]
	 */
	function kb_sidebar_blocks() {
		return \KentaBlocks\Store::get( 'kenta-sidebar-blocks', function () {

			$blocks = array();

			$sidebars = wp_get_sidebars_widgets();
			global $wp_registered_widgets;

			foreach ( $sidebars as $sidebar_id => $widgets ) {
				// TODO: filter invisible sidebar
				foreach ( $widgets as $widget_id ) {
					if ( ! isset( $wp_registered_widgets[ $widget_id ] ) ) {
						continue;
					}

					$instance = $wp_registered_widgets[ $widget_id ]['callback'][0];
					if ( ! $instance instanceof \WP_Widget_Block ) {
						continue;
					}

					foreach ( $instance->get_settings() as $setting ) {
						if ( ! isset( $setting['content'] ) ) {
							continue;
						}

						$blocks = array_merge( $blocks, kenta_blocks_parse_content( $setting['content'] ) );
					}
				}
			}

			return $blocks;
		} );
	}
}

if ( ! function_exists( 'kb_post_blocks' ) ) {
	/**
	 * Get all blocks in the post
	 *
	 * @param null $post
	 *
	 * @return array|false|mixed|null
	 */
	function kb_post_blocks( $post = null ) {
		if ( ! $post ) {
			global $post;
		}

		if ( is_object( $post ) ) {
			return \KentaBlocks\Store::get( "kenta-post-{$post->post_type}-{$post->ID}-blocks", function () use ( $post ) {
				$blocks = kenta_blocks_parse_content( $post->post_content );

				return is_array( $blocks ) ? $blocks : array();
			} );
		}

		return array();
	}
}

if ( ! function_exists( 'kb_wp_filesystem' ) ) {
	/**
	 *  Get global filesystem instance
	 *
	 * @return \WP_Filesystem_Direct
	 */
	function kb_wp_filesystem() {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem;
	}
}
