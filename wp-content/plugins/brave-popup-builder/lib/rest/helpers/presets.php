<?php

function bravepop_get_presets( $type='', $presetID='' ){
   $currentSettings = get_option('_bravepopup_settings');
   $currentPresets = $currentSettings && isset($currentSettings['presets']) ? $currentSettings['presets'] : array();

   if($type === 'all'){
       
      $presets = array();
      if($currentPresets){
         foreach ($currentPresets as $index=>$preset) { 
            $presets[$index] = new stdClass();
            $presets[$index]->id = $preset->id; 
            $presets[$index]->steps = $preset->steps && count($preset->steps) ? count($preset->steps) : 1;
            $presets[$index]->image = $preset->image;
            $presets[$index]->imageID = $preset->imageID;
            $presets[$index]->lastModified = $preset->lastModified;
         }
      }

      return new WP_REST_Response($presets);
   }

   if($type === 'single' && $presetID){
       $presetIndex = null;
       foreach ($currentPresets as $index=>$preset) {
            if ($preset->id === $presetID) {  $presetIndex = $index;  } 
       }
       if(isset($presetIndex) && $currentPresets[$presetIndex]){
           return new WP_REST_Response(array('preset'=>$currentPresets[$presetIndex]));
       }
   }

}

function bravepop_update_presets( $preset='', $presetAction='', $presetID='', $presetImageID='' ){
   //error_log('update_presets Called!!!!');
   $currentSettings = get_option('_bravepopup_settings');
   $currentPresets = $currentSettings && isset($currentSettings['presets']) ? $currentSettings['presets'] : array() ;

   if($preset){
       //$currentSettings = BravePopup_Settings::get_settings();
       $thePreset = json_decode($preset);
       $presetIndex = false;
       if($presetID){
           foreach ($currentPresets as $key=>$prst) { if ($prst->id === $presetID) {  $presetIndex = $key;  } }
       }
       //Alternative: $presetIndex = array_search($thePreset->id, array_column($currentPresets, 'id'))

       //error_log(wp_json_encode($currentSettings['presets']));
       if($presetAction === 'save'){ 
           if($presetIndex === false){
               $currentPresets[] = $thePreset;
               //error_log('Preset DOES NOT Exist!');
               BravePopup_Settings::save_settings( array('presets' => $currentPresets) );
               return new WP_REST_Response('Preset Saved Successfully.');
           }
       }
       if($presetAction === 'overwrite' && $presetID){
           //error_log('1. OVERWRITE PRESET!');
           if($presetIndex !== false){
               //error_log('2. OVERWRITE PRESET!');

               if(isset($presetImageID) && wp_get_attachment_image($presetImageID)){
                   wp_delete_attachment( $presetImageID, true );
                   //error_log('3. OVERWRITE PRESET! REMOVED OLD IMAGE');
               }

               $currentPresets[$presetIndex] = $thePreset;
               BravePopup_Settings::save_settings( array('presets' => $currentPresets) );
               return new WP_REST_Response('Preset Overwritten Successfully.');
           }
       }
   }

   if($presetAction === 'remove' && $presetID){
       $presetIndex = false;
       if($presetID){    foreach ($currentPresets as $key=>$prst) { if ($prst->id === $presetID) {  $presetIndex = $key;  } } }
       //error_log($presetIndex);
       //error_log(wp_json_encode($currentPresets));
       if($presetIndex !== false){
           //unset($currentPresets[$presetIndex]);
           
           //Remove The Preset Screenshot
           //error_log(wp_json_encode($currentPresets[$presetIndex]));
           //error_log($currentPresets[$presetIndex]->imageID);
           if(isset($currentPresets[$presetIndex]->imageID) && wp_get_attachment_image($currentPresets[$presetIndex]->imageID)){
               wp_delete_attachment( $currentPresets[$presetIndex]->imageID, true );
           }
           array_splice($currentPresets, $presetIndex, 1);
           //error_log(wp_json_encode($currentPresets));
           BravePopup_Settings::save_settings( array('presets' => $currentPresets) );
           return new WP_REST_Response('Preset Removed Successfully.');
       }else{
           return new WP_REST_Response('Error Removing Preset.');
       }
   }
   
   
}