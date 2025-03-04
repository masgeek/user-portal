<?php

/**
 * Theme: Live Stream.
 *
 * @link    https://plugins360.com
 * @since   1.6.4
 *
 * @package Automatic_YouTube_Gallery
 */

$player_width = ! empty( $attributes['player_width'] ) ? (int) $attributes['player_width'] . 'px' : '100%';
$player_ratio = ! empty( $attributes['player_ratio'] ) ? (float) $attributes['player_ratio'] : '56.25';

$featured = $videos[0]; // Featured Video
?>
<div id="ayg-<?php echo esc_attr( $attributes['uid'] ); ?>" class="ayg ayg-theme ayg-theme-livestream">
    <div class="ayg-player">
        <div class="ayg-player-container" style="max-width: <?php echo $player_width; ?>;">
            <?php
            $attributes['poster'] = sprintf( 'https://i.ytimg.com/vi/%s/0.jpg', esc_attr( $featured->id ) );       
                
            if ( 56.25 == $player_ratio ) { // 16:9 ( medium - 320x180, maxres - 1280x720 )
                $attributes['poster'] = sprintf( 'https://i.ytimg.com/vi/%s/maxresdefault.jpg', esc_attr( $featured->id ) );  
            } 

            the_ayg_player( $featured, $attributes ); 
            ?>            
        </div>
    </div>
</div>
