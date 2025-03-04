<?php

/**
 * Theme: Classic.
 *
 * @link    https://plugins360.com
 * @since   1.0.0
 *
 * @package Automatic_YouTube_Gallery
 */

$player_width = ! empty( $attributes['player_width'] ) ? (int) $attributes['player_width'] . 'px' : '100%';

$featured = $videos[0]; // Featured Video
?>
<div id="ayg-<?php echo esc_attr( $attributes['uid'] ); ?>" class="ayg ayg-theme ayg-theme-single">
    <div class="ayg-player">
        <div class="ayg-player-container" style="max-width: <?php echo $player_width; ?>;">
            <?php the_ayg_player( $featured, $attributes ); ?>
        </div>
        <div class="ayg-player-caption">
            <?php if ( ! empty( $attributes['player_title'] ) ) : ?>    
                <h2 class="ayg-player-title"><?php echo esc_html( $featured->title ); ?></h2>  
            <?php endif; ?>
            <?php if ( ! empty( $attributes['player_description'] ) && ! empty( $featured->description ) ) : ?>  
                <ayg-description class="ayg-player-description"><?php echo wp_kses_post( ayg_get_player_description( $featured ) ); ?></ayg-description>
            <?php endif; ?>
        </div>
    </div>
</div>
