<?php
/**
 * Plugin settings
 *
 * @package Kenta Blocks
 */

namespace KentaBlocks;

final class Settings {

	/**
	 * Member Variable
	 *
	 * @var Css
	 */
	private static $instance;

	/**
	 * All settings
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * @param array $settings
	 */
	private function __construct( array $settings = [] ) {
		$this->add_settings( $settings );
	}

	/**
	 * Batch add settings
	 *
	 * @param $settings
	 */
	public function add_settings( $settings ) {
		foreach ( $settings as $id => $args ) {
			$this->add_setting( $id, $args );
		}
	}

	/**
	 * Add single setting
	 *
	 * @param $id
	 * @param $args
	 */
	public function add_setting( $id, $args ) {
		$default = $args['default'] ?? null;
		$value   = get_option( $id, $default );

		$this->settings[ $id ] = array(
			'sanitize' => $args['sanitize'] ?? null,
			'default'  => $default,
			'value'    => $value
		);
	}

	/**
	 *  Initiator
	 */
	public static function get_instance( array $settings = [] ) {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self( $settings );
		}

		return self::$instance;
	}

	/**
	 * Get all settings
	 *
	 * @return array
	 */
	public function all() {
		return $this->settings;
	}

	/**
	 * Get all setting's value
	 *
	 * @return array
	 */
	public function values() {
		return array_map( function ( $item ) {
			return $item['value'];
		}, $this->settings );
	}

	/**
	 * Get setting value
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function value( $key ) {
		$setting = $this->get( $key );

		return $setting !== null ? $setting['value'] : null;
	}

	/**
	 * Get setting args
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function get( $key ) {
		return $this->settings[ $key ] ?? null;
	}

	/**
	 * Save setting
	 *
	 * @param $key
	 * @param $value
	 */
	public function save( $key, $value ) {
		$setting = $this->get( $key );
		if ( $setting == null ) {
			return;
		}

		$sanitize = $setting['sanitize'] ?? null;
		if ( $sanitize ) {
			$value = call_user_func( $sanitize, $value );
		}

		update_option( $key, $value );
		$this->settings[ $key ]['value'] = $value;
	}
}