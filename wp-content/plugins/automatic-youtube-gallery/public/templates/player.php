<?php

/**
 * Player
 *
 * @link    https://plugins360.com
 * @since   2.5.0
 *
 * @package Automatic_YouTube_Gallery
 */

$player_ratio = ! empty( $attributes['player_ratio'] ) ? (float) $attributes['player_ratio'] : '56.25';

$video_attributes = array(
    'src'   => esc_url( ayg_get_youtube_embed_url( $video->id, $attributes ) ),
    'ratio' => $player_ratio
);

if ( isset( $video->title ) ) {
    $video_attributes['title'] = esc_attr( $video->title );
}

if ( isset( $attributes['poster'] ) && ! empty( $attributes['poster'] ) ) {
    $video_attributes['poster'] = $attributes['poster'];
} else {
    if ( isset( $video->thumbnails->default ) ) {
        $video_attributes['poster'] = $video->thumbnails->default->url;
    }    

    if ( 75 == $player_ratio ) { // 4:3 ( default - 120x90, high - 480x360, standard - 640x480 )
        if ( isset( $video->thumbnails->high ) ) {
            $video_attributes['poster'] = $video->thumbnails->high->url;
        }

        if ( isset( $video->thumbnails->standard ) ) {
            $video_attributes['poster'] = $video->thumbnails->standard->url;
        }
    }    

    if ( 56.25 == $player_ratio ) { // 16:9 ( medium - 320x180, maxres - 1280x720 )
        if ( isset( $video->thumbnails->medium ) ) {
            $video_attributes['poster'] = $video->thumbnails->medium->url;
        }

        if ( isset( $video->thumbnails->maxres ) ) {
            $video_attributes['poster'] = $video->thumbnails->maxres->url;
        }
    }
}

if ( isset( $video_attributes['poster'] ) ) {
    $video_attributes['poster'] = esc_url( $video_attributes['poster'] );
}

if ( ! empty( $attributes['lazyload'] ) ) {
    $video_attributes['lazyload'] = '';
}

$player_html = sprintf( '<ayg-player %s></ayg-player>', ayg_combine_video_attributes( $video_attributes ) );
echo $player_html;