<?php

namespace KentaBlocks;

class Store {

	/**
	 * Save all runtime vars here
	 *
	 * @var array
	 */
	private static $vars = [];

	/**
	 * Get an item
	 *
	 * @param $key
	 * @param null $callback
	 *
	 * @return false|mixed|null
	 */
	static public function get( $key, $callback = null ) {
		if ( isset( self::$vars[ $key ] ) ) {
			return self::$vars[ $key ];
		}

		if ( ! is_callable( $callback ) ) {
			return $callback;
		}

		self::$vars[ $key ] = call_user_func( $callback );

		return self::$vars[ $key ];
	}

	/**
	 * Keep it here
	 *
	 * @param $key
	 * @param $value
	 */
	static public function keep( $key, $value ) {
		self::$vars[ $key ] = $value;
	}

	/**
	 * Did we keep this?
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	static public function has( $key ) {
		return isset( self::$vars[ $key ] );
	}

	/**
	 * Abandon it
	 *
	 * @param $key
	 */
	static public function abandon( $key ) {
		unset( self::$vars[ $key ] );
	}
}