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
$columns = (int) $attributes['columns'];

$params = array(  
    'uid'                => sanitize_text_field( $attributes['uid'] ),
    'loop'               => (int) $attributes['loop'],
    'autoadvance'        => (int) $attributes['autoadvance'],
    'player_title'       => (int) $attributes['player_title'],
    'player_description' => (int) $attributes['player_description']
);

$featured = $videos[0]; // Featured Video
?>
<div class="automatic-youtube-gallery ayg">
    <ayg-theme-classic id="ayg-<?php echo esc_attr( $attributes['uid'] ); ?>" class="ayg-theme ayg-theme-classic" data-params='<?php echo wp_json_encode( $params ); ?>'>
        <div class="ayg-player">
            <div class="ayg-player-container" style="max-width: <?php echo $player_width; ?>;">
                <?php
                unset( $attributes['loop'] );
                the_ayg_player( $featured, $attributes ); 
                ?>           
            </div>
            <div class="ayg-player-caption">
                <?php if ( ! empty( $attributes['player_title'] ) ) : ?>    
                    <h2 class="ayg-player-title"><?php echo esc_html( $featured->title ); ?></h2>  
                <?php endif; ?>
                <?php if ( ! empty( $attributes['player_description'] ) ) : ?>  
                    <ayg-description class="ayg-player-description"><?php if ( ! empty( $featured->description ) ) echo wp_kses_post( ayg_get_player_description( $featured ) ); ?></ayg-description>
                <?php endif; ?>
            </div>
        </div>
        <div class="ayg-videos ayg-gallery ayg-row">
            <?php foreach ( $videos as $index => $video ) :
                $classes = array(); 
                $classes[] = 'ayg-video';
                $classes[] = 'ayg-video-' . $video->id;
                $classes[] = 'ayg-col';
                $classes[] = 'ayg-col-' . $columns;
                if ( $columns > 3 ) $classes[] = 'ayg-col-sm-3';
                if ( $columns > 2 ) $classes[] = 'ayg-col-xs-2';

                if ( $video->id == $featured->id ) {
                    $classes[] = 'ayg-active';
                }
                ?>
                <div class="<?php echo implode( ' ', $classes ); ?>">
                    <?php the_ayg_gallery_thumbnail( $video, $attributes ); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php the_ayg_pagination( $attributes ); ?>
    </ayg-theme-classic>
</div>
