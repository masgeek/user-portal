<?php
/**
 * Icons Manager
 *
 * @package Kenta Blocks
 */

namespace KentaBlocks;

class IconsManager {

	/**
	 * @var null|array
	 */
	protected static $_fontawesome = null;

	/**
	 * @var array
	 */
	protected static $_fa_library = [];

	/**
	 * Get all libraries
	 *
	 * @return array
	 */
	public static function allLibraries() {
		return [
			'fa-regular' => [
				'icons' => self::faLibrary( 'regular' ),
			],
			'fa-solid'   => [
				'icons' => self::faLibrary( 'solid' ),
			],
			'fa-brands'  => [
				'icons' => self::faLibrary( 'brands' ),
			],
		];
	}

	/**
	 * @param $library
	 *
	 * @return array|mixed
	 */
	public static function faLibrary( $library ) {
		$library = substr( $library, 0, 1 );

		if ( ! isset( $_fa_library[ $library ] ) ) {
			$_fa_library[ $library ] = [];

			foreach ( self::fontawesome() as $icon => $data ) {
				if ( in_array( $library, $data['s'] ) ) {
					$_fa_library[ $library ][ $icon ] = [
						'value' => "fa{$library} fa-{$icon}"
					];
				}
			}
		}

		return $_fa_library[ $library ];
	}

	/**
	 * @return array
	 */
	public static function fontawesome() {
		if ( self::$_fontawesome === null ) {
			self::$_fontawesome = json_decode( kb_wp_filesystem()->get_contents( KENTA_BLOCKS_PLUGIN_PATH . 'assets/fontawesome.json' ), true );
		}

		return self::$_fontawesome;
	}
}
