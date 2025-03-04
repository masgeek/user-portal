<?php

/**
 * Pagination
 *
 * @link    https://plugins360.com
 * @since   1.0.0
 *
 * @package Automatic_YouTube_Gallery
 */

// Build attributes		
$source_type = sanitize_text_field( $attributes['type'] );

$params = array(
    'uid'                  => sanitize_text_field( $attributes['uid'] ),
    'post_id'              => (int) $attributes['post_id'],
    'type'                 => $source_type,
    'src'                  => sanitize_text_field( $attributes[ $source_type ] ),
    'order'                => sanitize_text_field( $attributes['order'] ), // works only when type=search
    'per_page'             => (int) $attributes['per_page'],
    'cache'                => (int) $attributes['cache'],
    'columns'              => ! empty( $attributes['columns'] ) ? (int) $attributes['columns'] : 1,
    'thumb_ratio'          => ! empty( $attributes['thumb_ratio'] ) ? (float) $attributes['thumb_ratio'] : 56.25,
    'thumb_title'          => ! empty( $attributes['thumb_title'] ) ? (int) $attributes['thumb_title'] : 0,
    'thumb_title_length'   => ! empty( $attributes['thumb_title_length'] ) ? (int) $attributes['thumb_title_length'] : 0,
    'thumb_excerpt'        => ! empty( $attributes['thumb_excerpt'] ) ? (int) $attributes['thumb_excerpt'] : 0,
    'thumb_excerpt_length' => ! empty( $attributes['thumb_excerpt_length'] ) ? (int) $attributes['thumb_excerpt_length'] : 0,
    'player_description'   => ! empty( $attributes['player_description'] ) ? (int) $attributes['player_description'] : 0,	
    'total_pages'          => 1,		
    'paged'                => 1,	
    'next_page_token'      => ! empty( $attributes['next_page_token'] ) ? sanitize_text_field( $attributes['next_page_token'] ) : '',
    'prev_page_token'      => ! empty( $attributes['prev_page_token'] ) ? sanitize_text_field( $attributes['prev_page_token'] ) : ''
);

// Find total number of pages
$videos_found = ! empty( $attributes['videos_found'] ) ? (int) $attributes['videos_found'] : 0;

if ( $videos_found > 0 ) {
    if ( 'search' == $source_type ) {
        $limit = min( (int) $attributes['limit'], $videos_found );
        $params['total_pages'] = ceil( $limit / (int) $attributes['per_page'] );
    } else {
        $params['total_pages'] = ceil( $videos_found / (int) $attributes['per_page'] );
    }
}		

// Process output
if ( $params['total_pages'] <= 1 ) {
    return false;
}

$params = apply_filters( 'ayg_pagination_args', $params, $attributes );
?>
<ayg-pagination class="ayg-pagination" data-params='<?php echo wp_json_encode( $params ); ?>'>
    <?php if ( 'pager' == $attributes['pagination_type'] ) : // pager ?>
        <div class="ayg-pagination-prev">
            <button type="button" class="ayg-btn ayg-pagination-prev-btn" data-type="previous" style="display: none;"><?php echo esc_html( $attributes['previous_button_label'] ); ?></button>
        </div>
        <div class="ayg-pagination-info">
            <span class="ayg-pagination-current-page-number">1</span>
            <?php esc_html_e( 'of', 'automatic-youtube-gallery' ); ?>
            <span class="ayg-pagination-total-pages"><?php echo (int) $params['total_pages']; ?></span>
        </div>
        <div class="ayg-pagination-next">
            <button type="button" class="ayg-btn ayg-pagination-next-btn" data-type="next"><?php echo esc_html( $attributes['next_button_label'] ); ?></button>
        </div>
    <?php else : // more ?>
        <div class="ayg-pagination-next">
            <button type="button" class="ayg-btn ayg-pagination-next-btn" data-type="more"><?php echo esc_html( $attributes['more_button_label'] ); ?></button>
        </div>
    <?php endif; ?>
</ayg-pagination>