<?php
class BravePopup_Settings {
	/**
	 * Option key to save settings
	 *
	 * @var string
	 */
	protected static $option_key = '_bravepopup_settings';
	/**
	 * Default settings
	 *
	 * @var array
	 */
	protected static $defaults = array(
      'integrations' => array(),
      'emailvalidator'=> array('active'=>'disabled'),
      'license' => '',
      'presets' => array(),
      'visibility'=> array(),
      'goals'=> array(),
      'submission' => array(),
      'settings' => array(),
      'app_settings' => '',
      'analytics' => array(),
      'fonts' => array(),
      'welcome_tour' => 'false',
      'geodb_update'=>''
	);
	/**
	 * Get saved settings
	 *
	 * @return array
	 */
	public static function get_settings(){
		$saved = get_option( self::$option_key, array() );
		if( ! is_array( $saved ) || ! empty( $saved )){
			return self::$defaults;
		}
		return wp_parse_args( $saved, self::$defaults );
	}
	/**
	 * Save settings
	 *
	 * Array keys must be whitelisted (IE must be keys of self::$defaults
	 *
	 * @param array $settings
	 */
	public static function save_settings( array  $settings ){
		//remove any non-allowed indexes before save
      //error_log(wp_json_encode($settings));
      $saved = get_option( self::$option_key, array() );

		foreach ( $settings as $i => $setting ){
			if( ! array_key_exists( $i, self::$defaults ) ){
				unset( $settings[ $i ] );
			}else{
            $saved[$i] =  $setting;
         }
      }

      update_option( self::$option_key, $saved );
      
	}
}