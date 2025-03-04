<?php

$attributes = array_merge( array(
    'blockID'          => array(
        'type' => 'string',
    ),
    'slidesToShow'     => array(
        'type'    => 'object',
        'default' => 1,
    ),
    'slidesToScroll'   => array(
        'type'    => 'number',
        'default' => 1,
    ),
    'slidesGap'        => array(
        'type'    => 'object',
        'default' => '0px',
    ),
    'effect'           => array(
        'type'    => 'string',
        'default' => 'slide',
    ),
    'cssEase'          => array(
        'type'    => 'string',
        'default' => 'ease',
    ),
    'duration'         => array(
        'type'    => 'number',
        'default' => 700,
    ),
    'infinite'         => array(
        'type'    => 'string',
        'default' => 'yes',
    ),
    'autoplay'         => array(
        'type'    => 'string',
        'default' => 'no',
    ),
    'pauseOnHover'     => array(
        'type'    => 'string',
        'default' => 'yes',
    ),
    'autoplaySpeed'    => array(
        'type'    => 'number',
        'default' => 5000,
    ),
    'prevArrow'        => array(
        'type'      => 'string',
        'source'    => 'attribute',
        'selector'  => '.yuki-slides-prev-arrow i',
        'attribute' => 'class',
        'default'   => 'fas fa-chevron-left',
    ),
    'nextArrow'        => array(
        'type'      => 'string',
        'source'    => 'attribute',
        'selector'  => '.yuki-slides-next-arrow i',
        'attribute' => 'class',
        'default'   => 'fas fa-chevron-right',
    ),
    'navigation'       => array(
        'type'    => 'string',
        'default' => 'yes',
    ),
    'navigationMotion' => array(
        'type'    => 'string',
        'default' => 'no',
    ),
    'pagination'       => array(
        'type'    => 'string',
        'default' => 'yes',
    ),
    'paginationMotion' => array(
        'type'    => 'string',
        'default' => 'no',
    ),
), kenta_blocks_advanced_attrs() );
$metadata = array(
    'title'       => __( 'Slides (KB)', 'kenta-blocks' ),
    'description' => __( 'Display a carousel with any blocks in the slides.', 'kenta-blocks' ),
    'keywords'    => array(
        'slider',
        'slick',
        'carousel',
        'slides',
        'side'
    ),
    'supports'    => array(
        'anchor' => true,
        'align'  => array('wide', 'full'),
        'html'   => false,
    ),
    'attributes'  => $attributes,
);
return array(
    'metadata'     => $metadata,
    'dependencies' => function ( $block ) {
        return [['slick', 'kenta-blocks-frontend-script'], ['slick']];
    },
    'css'          => function ( $block, $css ) {
        $id = kb_get_block_attr( $block, 'blockID' );
        $css[".kb-slides.kb-slides-{$id}"] = array_merge( array(
            '--kb-slides-items-gutter' => kb_get_block_attr( $block, 'slidesGap' ),
        ), kenta_blocks_advanced_css( $block ) );
        return $css;
    },
    'script'       => function ( $block ) {
        $id = kb_esc_block_id( kb_get_block_attr( $block, 'blockID' ) );
        $slidesShow = kenta_blocks_script()->sanitizeResponsiveValue( kb_get_block_attr( $block, 'slidesToShow' ), 1 );
        $slidesScroll = absint( kb_get_block_attr( $block, 'slidesToScroll' ) );
        $isFade = kb_get_block_attr( $block, 'effect' ) === 'fade';
        $options = wp_json_encode( array(
            'rtl'            => is_rtl(),
            'infinite'       => kb_get_block_attr( $block, 'infinite' ) === 'yes',
            'fade'           => 1 == absint( $slidesShow['desktop'] ) && $isFade,
            'speed'          => absint( kb_get_block_attr( $block, 'duration' ) ),
            'arrows'         => kb_get_block_attr( $block, 'navigation' ) === 'yes',
            'dots'           => kb_get_block_attr( $block, 'pagination' ) === 'yes',
            'cssEase'        => kb_get_block_attr( $block, 'cssEase' ),
            'autoplay'       => kb_get_block_attr( $block, 'autoplay' ) === 'yes',
            'autoplaySpeed'  => absint( kb_get_block_attr( $block, 'autoplaySpeed' ) ),
            'pauseOnHover'   => kb_get_block_attr( $block, 'pauseOnHover' ) === 'yes',
            'prevArrow'      => '#kb-slides-prev-' . $id,
            'nextArrow'      => '#kb-slides-next-' . $id,
            'slidesToShow'   => max( absint( $slidesShow['desktop'] ), 1 ),
            'slidesToScroll' => $slidesScroll,
            'responsive'     => [[
                'breakpoint' => absint( kenta_blocks_css()->desktop() ),
                'settings'   => [
                    'slidesToShow'   => max( absint( $slidesShow['tablet'] ), 1 ),
                    'slidesToScroll' => ( $slidesScroll > absint( $slidesShow['tablet'] ) ? 1 : $slidesScroll ),
                    'fade'           => 1 == absint( $slidesShow['tablet'] ) && $isFade,
                ],
            ], [
                'breakpoint' => absint( kenta_blocks_css()->tablet() ),
                'settings'   => [
                    'slidesToShow'   => max( absint( $slidesShow['mobile'] ), 1 ),
                    'slidesToScroll' => ( $slidesScroll > absint( $slidesShow['mobile'] ) ? 1 : $slidesScroll ),
                    'fade'           => 1 == absint( $slidesShow['mobile'] ) && $isFade,
                ],
            ]],
        ) );
        return "createKBSlides('{$id}', {$options});";
    },
);