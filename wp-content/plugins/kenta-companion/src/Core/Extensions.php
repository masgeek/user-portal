<?php

namespace KentaCompanion\Core;

/**
 * Kenta theme extensions manager
 */
class Extensions {

	/**
	 * All extensions
	 *
	 * @var array
	 */
	private $extensions = [];

	/**
	 * Boostrap all extensions
	 *
	 * @return void
	 */
	public function bootstrap() {
		foreach ( $this->extensions as $extension ) {
			new $extension['class']();
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'customize_preview_init', [ $this, 'enqueue_preview_scripts' ] );
	}

	/**
	 * Register extensions
	 *
	 * @param string $id
	 * @param array $args
	 *
	 * @return Extensions
	 */
	public function register( $id, $args ) {
		$this->extensions[ $id ] = $args;

		return $this;
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		$suffix = defined( 'WP_DEBUG' ) && WP_DEBUG ? '' : '.min';

		wp_enqueue_style(
			'kenta-cmp-style',
			KCMP_ASSETS_URL . 'css/kenta-companion' . $suffix . '.css',
			[],
			KCMP_VERSION
		);

		$assets = array();
		if ( file_exists( KCMP_ASSETS_PATH . 'js/kenta-companion.asset.php' ) ) {
			$assets = require KCMP_ASSETS_PATH . 'js/kenta-companion.asset.php';
		}

		wp_enqueue_script(
			'kenta-cmp-script',
			KCMP_ASSETS_URL . 'js/kenta-companion' . $suffix . '.js',
			$assets['dependencies'] ?? array(),
			$assets['version'] ?? KCMP_VERSION
		);

	}

	/**
	 * Enqueue preview scripts
	 */
	public function enqueue_preview_scripts() {
		$suffix = defined( 'WP_DEBUG' ) && WP_DEBUG ? '' : '.min';

		wp_enqueue_script(
			'kenta-cmp-customizer-preview-script',
			KCMP_ASSETS_URL . 'js/customizer-preview' . $suffix . '.js',
			array( 'customize-preview', 'customize-selective-refresh' ),
			KCMP_VERSION
		);
	}
}