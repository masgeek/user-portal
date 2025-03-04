<?php
/**
 * Helper functions
 *
 * @package Kenta Companion
 */

use KentaCompanion\Core\KentaCompanion;

if ( ! function_exists( 'kcmp' ) ) {
	/**
	 * Get global application
	 *
	 * @param null $abstract
	 * @param array $parameters
	 *
	 * @return \Illuminate\Container\Container|mixed|object
	 */
	function kcmp( $abstract = null, array $parameters = [] ) {

		if ( is_null( $abstract ) ) {
			return KentaCompanion::getInstance();
		}

		return KentaCompanion::getInstance()->make( $abstract, $parameters );
	}
}

if ( ! function_exists( 'kcmp_notices' ) ) {
	/**
	 * Get notices instance
	 *
	 * @return mixed|\Wpmoose\WpDismissibleNotice\Notices
	 */
	function kcmp_notices() {
		return \Wpmoose\WpDismissibleNotice\Notices::instance(
			'kcmp',
			KCMP_PLUGIN_URL . 'vendor/wpmoose/wp-dismissible-notice/'
		);
	}
}

if ( ! function_exists( 'kcmp_get_template_part' ) ) {
	/**
	 * Include template
	 *
	 * @param $slug
	 */
	function kcmp_get_template_part( $slug ) {
		$path = KCMP_PLUGIN_PATH . 'templates/' . $slug . '.php';
		if ( file_exists( $path ) ) {
			require $path;
		}
	}
}

if ( ! function_exists( 'kcmp_current_template' ) ) {
	/**
	 * Get current template name
	 *
	 * @return string
	 */
	function kcmp_current_template() {
		return strtolower( str_replace( '-premium', '', get_option( 'template' ) ) );
	}
}

if ( ! function_exists( 'kcmp_is_valid_theme' ) ) {
	/**
	 * Is valid kenta theme or child theme
	 *
	 * @return bool
	 */
	function kcmp_is_valid_theme() {
		return kcmp_current_template() === 'kenta';
	}
}

if ( ! function_exists( 'kcmp_register_file_as_media_attachment' ) ) {
	/**
	 * Register file as attachment to the Media page.
	 *
	 * @param string $path log file path.
	 *
	 * @return int|WP_Error
	 */
	function kcmp_register_file_as_media_attachment( $path ) {
		// Check the type of file.
		$mimes    = array( 'txt' => 'text/plain' );
		$filetype = wp_check_filetype( basename( $path ), apply_filters( 'kcmp/file_mimes', $mimes ) );

		// Prepare an array of post data for the attachment.
		$attachment = array(
			'guid'           => kenta_upload_file_url( $path ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => apply_filters( 'kcmp/attachment_prefix', esc_html__( 'Kenta Template Import - ', 'kenta-companion' ) ) . preg_replace( '/\.[^.]+$/', '', basename( $path ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		// Insert the file as attachment in Media page.
		return wp_insert_attachment( $attachment, $path );
	}
}

if ( ! function_exists( 'kenta_upload_file_url' ) ) {
	/**
	 * Get log file url
	 *
	 * @param string $path log path to use for the log filename.
	 *
	 * @return string, url to the log file.
	 */
	function kenta_upload_file_url( $path ) {
		$upload_dir = wp_upload_dir();
		$upload_url = apply_filters( 'kcmp/upload_file_url', trailingslashit( $upload_dir['url'] ) );

		return $upload_url . basename( $path );
	}
}

if ( ! function_exists( 'kcmp_plugin_setup_page' ) ) {
	/**
	 * Get the plugin page setup data.
	 *
	 * @return array
	 */
	function kcmp_plugin_setup_page() {
		return apply_filters( 'kcmp/plugin_setup_page', [
			'page_title' => esc_html__( 'Kenta Companion', 'kenta-companion' ),
			'menu_title' => esc_html__( 'Kenta', 'kenta-companion' ),
			'capability' => 'manage_options',
			'menu_slug'  => 'kenta-companion',
		] );
	}
}

if ( ! function_exists( 'kcmp_plugin_starter_page' ) ) {
	/**
	 * Get the plugin starter template page setup data.
	 *
	 * @return array
	 */
	function kcmp_plugin_starter_page() {
		return apply_filters( 'kcmp/plugin_starter_page', [
			'parent_slug' => 'kenta-companion',
			'page_title'  => esc_html__( 'Starter Sites', 'kenta-companion' ),
			'menu_title'  => esc_html__( 'Starter Sites', 'kenta-companion' ),
			'capability'  => 'switch_themes',
			'menu_slug'   => 'kenta-starter-sites',
		] );
	}
}

if ( ! function_exists( 'kcmp_plugin_optin_page' ) ) {
	/**
	 * Get the plugin optin template page setup data.
	 *
	 * @return array
	 */
	function kcmp_plugin_optin_page() {
		return apply_filters( 'kcmp/plugin_optin_page', [
			'parent_slug' => 'kenta-companion',
			'page_title'  => esc_html__( 'Opt In', 'kenta-companion' ),
			'menu_title'  => esc_html__( 'Opt In', 'kenta-companion' ),
			'capability'  => 'switch_themes',
			'menu_slug'   => 'kenta-companion-optin',
		] );
	}
}

if ( ! function_exists( 'kcmp_deactivate_classic_editor' ) ) {
	/**
	 * Deactivate classic editor
	 *
	 * @param bool $silent
	 */
	function kcmp_deactivate_classic_editor( $silent = true ) {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die(
				'<h1>' . __( 'You need a higher level of permission.', 'kenta-companion' ) . '</h1>' .
				'<p>' . __( 'Sorry, you are not allowed to deactivate plugins on this site.', 'kenta-companion' ) . '</p>',
				403
			);
		}

		if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			deactivate_plugins( array( 'classic-editor/classic-editor.php' ) );
		}

		if ( ! $silent ) {
			wp_redirect( admin_url( 'plugins.php' ) );
			exit();
		}
	}
}

if ( ! function_exists( 'kcmp_install_plugin' ) ) {
	/**
	 * Install plugin
	 *
	 * @param string $slug
	 * @param string $plugin
	 *
	 * @return mixed
	 */
	function kcmp_install_plugin( $slug, $plugin ) {
		$slug   = sanitize_key( wp_unslash( $slug ) );
		$plugin = plugin_basename( sanitize_text_field( wp_unslash( $plugin ) ) );

		$status = array(
			'install' => 'plugin',
			'slug'    => $slug,
		);

		if ( ! current_user_can( 'install_plugins' ) ) {
			$status['errorMessage'] = __( 'Sorry, you are not allowed to install plugins on this site.', 'kenta-companion' );

			return $status;
		}

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/file.php';

		// Looks like a plugin is installed
		if ( file_exists( WP_PLUGIN_DIR . '/' . $slug ) ) {

			$plugin_data             = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
			$status['plugin']        = $plugin;
			$status['pluginVersion'] = $plugin_data['Version'];
			$status['pluginName']    = $plugin_data['Name'];

			// plugin is inactive.
			if ( current_user_can( 'activate_plugin', $plugin ) && is_plugin_inactive( $plugin ) ) {
				$result = activate_plugin( $plugin );

				if ( is_wp_error( $result ) ) {
					$status['errorCode']    = $result->get_error_code();
					$status['errorMessage'] = $result->get_error_message();

					return $status;
				}

				return $status;
			}
		}

		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => $slug,
				'fields' => array(
					'sections' => false,
				),
			)
		);

		if ( is_wp_error( $api ) ) {
			$status['errorMessage'] = $api->get_error_message();

			return $status;
		}

		if ( isset( $status['pluginVersion'] ) && version_compare( $status['pluginVersion'], $api->version, '>=' ) ) {
			return $status;
		}

		$status['pluginName'] = $api->name;

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link, array(
			'overwrite_package' => true,
		) );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$status['debug'] = $skin->get_upgrade_messages();
		}

		if ( is_wp_error( $result ) ) {
			$status['errorCode']    = $result->get_error_code();
			$status['errorMessage'] = $result->get_error_message();

			return $status;
		} elseif ( is_wp_error( $skin->result ) ) {
			$status['errorCode']    = $skin->result->get_error_code();
			$status['errorMessage'] = $skin->result->get_error_message();

			return $status;
		} elseif ( $skin->get_errors()->get_error_code() ) {
			$status['errorMessage'] = $skin->get_error_messages();

			return $status;
		} elseif ( is_null( $result ) ) {
			global $wp_filesystem;

			$status['errorCode']    = 'unable_to_connect_to_filesystem';
			$status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'kenta-companion' );

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
			}

			return $status;
		}

		$install_status = install_plugin_install_status( $api );

		if ( current_user_can( 'activate_plugin', $install_status['file'] ) && is_plugin_inactive( $install_status['file'] ) ) {
			$result = activate_plugin( $install_status['file'] );

			if ( is_wp_error( $result ) ) {
				$status['errorCode']    = $result->get_error_code();
				$status['errorMessage'] = $result->get_error_message();

				return $status;
			}
		}

		return $status;
	}
}

if ( ! function_exists( 'kcmp_localize_elementor_image' ) ) {
	/**
	 * Fix elementor image url error
	 *
	 * @param string $slug
	 *
	 * @return void
	 */
	function kcmp_localize_elementor_image( $slug ) {
		$args            = array(
			'post_type'      => [ 'page' ],
			'posts_per_page' => '-1',
			'meta_key'       => '_elementor_version'
		);
		$elementor_pages = new WP_Query ( $args );

		// Check that we have query results.
		if ( $elementor_pages->have_posts() ) {

			// Start looping over the query results.
			while ( $elementor_pages->have_posts() ) {

				$elementor_pages->the_post();

				// Replace Demo with Current
				$site_url      = get_site_url();
				$site_url      = str_replace( '/', '\/', $site_url );
				$demo_site_url = KCMP_DEMO_SITE_URL . $slug;
				$demo_site_url = str_replace( '/', '\/', $demo_site_url );

				// Elementor Data
				$data = get_post_meta( get_the_ID(), '_elementor_data', true );
				$data = json_encode( $data );

				if ( ! empty( $data ) ) {
					$data = preg_replace( '/\\\{1}\/sites\\\{1}\/\d+/', '', $data );
					$data = str_replace( $demo_site_url, $site_url, $data );
					$data = json_decode( $data, true );
				}

				update_metadata( 'post', get_the_ID(), '_elementor_data', $data );

				// Elementor Page Settings
				$page_settings = get_post_meta( get_the_ID(), '_elementor_page_settings', true );
				$page_settings = json_encode( $page_settings );

				if ( ! empty( $page_settings ) ) {
					$page_settings = preg_replace( '/\\\{1}\/sites\\\{1}\/\d+/', '', $page_settings );
					$page_settings = str_replace( $demo_site_url, $site_url, $page_settings );
					$page_settings = json_decode( $page_settings, true );
				}

				update_metadata( 'post', get_the_ID(), '_elementor_page_settings', $page_settings );

			}

		}

		// Clear Elementor Cache
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}

if ( ! function_exists( 'kcmp_upsell_url' ) ) {
	/**
	 * Upsell url
	 *
	 * @return string
	 */
	function kcmp_upsell_url() {
//		return admin_url( 'admin.php?page=kenta-companion-pricing' );
		return 'https://kentatheme.com/pricing/';
	}
}

if ( ! function_exists( 'kcmp_upsell_info' ) ) {
	/**
	 * @param $info
	 *
	 * @return string
	 */
	function kcmp_upsell_info( $info ) {
		$upsell_url = kcmp_upsell_url();

		return sprintf(
			$info, '<a target="_blank" href="' . esc_url( $upsell_url ) . '">', '</a>'
		);
	}
}

if ( ! function_exists( 'kcmp_upsell_info_control' ) ) {
	/**
	 * @param $info
	 * @param null $id
	 *
	 * @return \LottaFramework\Customizer\Controls\Info
	 */
	function kcmp_upsell_info_control( $info, $id = null ) {
		return ( new \LottaFramework\Customizer\Controls\Info( $id ) )
			->alignCenter()
			->hideBackground()
			->setInfo( kcmp_upsell_info( $info ) );
	}
}

if ( ! function_exists( 'kcmp_docs_control' ) ) {
	/**
	 * @param $info
	 * @param $url
	 *
	 * @return \LottaFramework\Customizer\Controls\Info
	 */
	function kcmp_docs_control( $info, $url, $id = null ) {
		return ( new \LottaFramework\Customizer\Controls\Info( $id ) )
			->alignCenter()
			->setInfo( sprintf(
				$info, '<a target="_blank" href="' . esc_url( $url ) . '">', '</a>'
			) );
	}
}

/**
 * Implementation for WordPress functions missing in older WordPress versions.
 *
 * @package Kenta Companion
 */

if ( ! function_exists( 'wp_slash_strings_only' ) ) {
	/**
	 * Adds slashes to only string values in an array of values.
	 *
	 * Compat for WordPress < 5.3.0.
	 *
	 * @param mixed $value Scalar or array of scalars.
	 *
	 * @return mixed Slashes $value
	 * @since 0.7.0
	 *
	 */
	function wp_slash_strings_only( $value ) {
		return map_deep( $value, 'addslashes_strings_only' );
	}
}

if ( ! function_exists( 'addslashes_strings_only' ) ) {
	/**
	 * Adds slashes only if the provided value is a string.
	 *
	 * Compat for WordPress < 5.3.0.
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 * @since 0.7.0
	 *
	 */
	function addslashes_strings_only( $value ) {
		return is_string( $value ) ? addslashes( $value ) : $value;
	}
}

if ( ! function_exists( 'map_deep' ) ) {
	/**
	 * Maps a function to all non-iterable elements of an array or an object.
	 *
	 * Compat for WordPress < 4.4.0.
	 *
	 * @param mixed $value The array, object, or scalar.
	 * @param callable $callback The function to map onto $value.
	 *
	 * @return mixed The value with the callback applied to all non-arrays and non-objects inside it.
	 * @since 0.7.0
	 *
	 */
	function map_deep( $value, $callback ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $index => $item ) {
				$value[ $index ] = map_deep( $item, $callback );
			}
		} elseif ( is_object( $value ) ) {
			$object_vars = get_object_vars( $value );
			foreach ( $object_vars as $property_name => $property_value ) {
				$value->$property_name = map_deep( $property_value, $callback );
			}
		} else {
			$value = call_user_func( $callback, $value );
		}

		return $value;
	}
}

if ( ! function_exists( 'kcmp_is_premium_kb_installed' ) ) {
	/**
	 * Check is premium kenta blocks installed or not
	 *
	 * @return bool
	 */
	function kcmp_is_premium_kb_installed() {
		if ( function_exists( 'kb_fs' ) ) {
			return kb_fs()->can_use_premium_code();
		}

		return false;
	}
}
