<?php
/**
 * Frontend blocks dynamic script utils
 *
 * @package Kenta Blocks
 */

namespace KentaBlocks;

final class Script {
	/**
	 * Member Variable
	 *
	 * @var Script
	 */
	private static $instance;

	/**
	 * Constructor
	 */
	private function __construct() {
		//
	}

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Generate sidebar dynamic script
	 *
	 * @return mixed|string
	 */
	public function dynamicSidebarScriptRaw() {
		return $this->dynamicScripts( kb_sidebar_blocks() );
	}

	/**
	 * Get dynamic scripts from blocks
	 *
	 * @param $blocks
	 * @param string $scripts
	 *
	 * @return mixed|string
	 */
	public function dynamicScripts( $blocks, $scripts = '' ) {

		foreach ( $blocks as $block ) {
			if ( is_array( $block ) ) {

				$name = $block['blockName'];

				if ( '' === $name ) {
					continue;
				}

				if ( 'core/block' === $name ) {
					$id = ( isset( $block['attrs']['ref'] ) ) ? $block['attrs']['ref'] : 0;

					if ( $id ) {
						$content = get_post_field( 'post_content', $id );

						$reusable_blocks = kenta_blocks_parse_content( $content );

						$scripts .= $this->dynamicScripts( $reusable_blocks );

					}
				} else {
					$kenta_blocks = kenta_blocks_all();
					if ( isset( $kenta_blocks[ $name ]['script'] ) ) {

						if ( isset( $block['attrs']['blockID'] ) ) {
							$scripts .= $kenta_blocks[ $name ]['script']( $block );
						}
					}
				}

				if ( isset( $block['innerBlocks'] ) ) {
					$scripts .= $this->dynamicScripts( $block['innerBlocks'] );
				}
			}
		}

		return $scripts;
	}

	/**
	 * Get dynamic scripts form post
	 *
	 * @param null $post
	 *
	 * @return mixed|string
	 */
	public function dynamicScriptsRaw( $post = null ) {
		return $this->dynamicScripts( kb_post_blocks( $post ) );
	}

	/**
	 * @param $value
	 * @param null $default
	 *
	 * @return array|mixed[]|null[]
	 */
	public function sanitizeResponsiveValue( $value, $default = null ) {
		if ( is_array( $value ) ) {
			return array_merge( array(
				'desktop' => $default,
				'tablet'  => $default,
				'mobile'  => $default,
			), $value );
		}

		return [
			'desktop' => $value,
			'tablet'  => $default,
			'mobile'  => $default,
		];
	}
}