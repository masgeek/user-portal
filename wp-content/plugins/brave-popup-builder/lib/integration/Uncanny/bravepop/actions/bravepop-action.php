<?php

use Uncanny_Automator\Recipe;

/**
 * Class UOA_SHOWNOTICE
 */
class BRAVEPOP_ACTION {
	use Recipe\Actions;

	/**
	 * UOA_SHOWNOTICE constructor.
	 */
	public function __construct() {
		$this->setup_action();
      add_action( 'wp_head', [ $this, 'render_popup'], 9 );
	}

   protected function setup_action() {
		$this->set_integration( 'bravepop' );
		$this->set_action_code( 'OPENBRAVEPOPUP' );
		$this->set_action_meta( 'BRAVEPOPUPID' );
		/* translators: Action - WordPress */
		$this->set_sentence( sprintf( __( 'Open Popup {{Brave Popup:%s}}', 'bravepop' ), 'BRAVEPOPUPID' ));
		/* translators: Action - WordPress */
		$this->set_readable_sentence( esc_attr__( 'Open Popup {{Brave Popup}}', 'bravepop' ), 'BRAVEPOPUPID'  );

      $options = $this->bravepop_uncanny_all_brave_popups( $label = null, $option_code = 'BRAVEPOPUPID' );

      $this->set_options( $options );

		$this->register_action();
	}

   protected function bravepop_uncanny_all_brave_popups( $label = null, $option_code = 'BRAVEPOPUPID' ) {
 
      if ( ! $label ) {
         $label = esc_attr__( 'Popup', 'bravepop' );
      }
   
      //Load All Popups
      $args = array( 'numberposts' => -1, 'post_status'=> array('publish', 'draft')  , 'post_type' => 'popup', 'meta_query' => array( array( 'key' => 'popup_type', 'value' => 'popup','compare' => 'LIKE' ) )  );
      $options = Automator()->helpers->recipe->options->wp_query( $args, false, __( 'Any Popup', 'bravepop' ) );
   

      $option = array(Automator()->helpers->recipe->field->select(
         array(
            'option_code' => $option_code,
            'label'       => $label,
            'options'         => $options,
            'description' => 'Select the popup you want to show',
            'required'        => true,
            'input_type'  => 'select',
         )
      ));
   
      return $option;
   }
   

	/**
	 * @param int $user_id
	 * @param array $action_data
	 * @param int $recipe_id
	 * @param array $args
	 * @param $parsed
	 */
	protected function process_action(  $user_id, $action_data, $recipe_id, $args, $parsed ) {

      if ( isset( $action_data['meta'][ 'BRAVEPOPUPID' ] ) ) {

         $popup_id = $action_data['meta'][ 'BRAVEPOPUPID' ];
         $popup_id = absint( $popup_id );

         $uncanny_popups_actions = get_user_meta( $user_id, 'bravepop_uncanny_actions', true );
         $uncanny_popups_actions_array = $uncanny_popups_actions ? $uncanny_popups_actions : [];
         $uncanny_popups_actions_array[] = $popup_id;
         update_user_meta( $user_id, 'bravepop_uncanny_actions', $uncanny_popups_actions_array );
         
         Automator()->complete->action( $user_id, $action_data, $recipe_id );
      }
	}

   public function render_popup(){
      $user_id = get_current_user_id();
      if(!$user_id){ return; }
      
      global $bravepop_global;
      $uncanny_popups_actions = get_user_meta( $user_id, 'bravepop_uncanny_actions', true );
      $uncanny_popups_actions_array = $uncanny_popups_actions ? $uncanny_popups_actions : [];
      $uncannyPopups = $uncanny_popups_actions_array;
      $alreadyLoaded = false;

      if(isset($uncannyPopups) && is_array($uncannyPopups) && count($uncannyPopups) > 0){
         
         foreach ($uncannyPopups as $key => $popup_id) {
            
            $alreadyLoaded = false;
            foreach ($bravepop_global['current_popups'] as $key => $item) {
               if($alreadyLoaded === false && ($item->id === $popup_id && $item->status === 'published')){
                  $alreadyLoaded = true;
                  $currentPopups[] = $item->id;
               }
            }

            if($alreadyLoaded === false){
               $customContent = json_decode(get_post_meta($popup_id, 'popup_data', true));
               $customContent->settings->trigger->triggerType = 'load';
               new BravePop_Popup($popup_id, 'popup', false, false, wp_json_encode($customContent) ); //insert the popup to the current page
               $pop_key = array_search($popup_id, $uncanny_popups_actions_array);
               if($pop_key !== false){   unset($uncanny_popups_actions_array[$pop_key]);   }
               update_user_meta( $user_id, 'bravepop_uncanny_actions', $uncanny_popups_actions_array );
            }
         }

      }
   }

}