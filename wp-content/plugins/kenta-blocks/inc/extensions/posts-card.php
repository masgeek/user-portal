<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !function_exists( 'kb_posts_card_attrs' ) ) {
    /**
     * Post card style attributes
     *
     * @param array $defaults
     *
     * @return array
     */
    function kb_posts_card_attrs(  $defaults = array()  ) {
        $defaults = wp_parse_args( $defaults, array(
            'cardStyle' => 'plain',
        ) );
        return array(
            'cardStyle' => array(
                'type'    => 'string',
                'default' => $defaults['cardStyle'],
            ),
        );
    }

}
if ( !function_exists( 'kb_card_preset_style' ) ) {
    /**
     * Card preset style
     *
     * @param $block
     *
     * @return array|string[]
     */
    function kb_card_preset_style(  $block  ) {
        $preset = kb_get_block_attr( $block, 'cardStyle' );
        switch ( $preset ) {
            case 'ghost':
                return [
                    'background' => 'none',
                    'border'     => 'none',
                    'box-shadow' => 'none',
                ];
            case 'plain':
                return [
                    'background' => 'var(--kb-base-color)',
                    'border'     => 'none',
                    'box-shadow' => 'none',
                ];
            case 'bordered':
                return [
                    'background' => 'var(--kb-base-color)',
                    'border'     => '1px solid var(--kb-base-300)',
                    'box-shadow' => 'none',
                ];
            case 'shadowed':
                return [
                    'border'     => 'none',
                    'background' => 'var(--kb-base-color)',
                    'box-shadow' => '0 0 14px rgba(70,71,73,0.1)',
                ];
            case 'mixed':
                return [
                    'background' => 'var(--kb-base-color)',
                    'border'     => '1px solid var(--kb-base-300)',
                    'box-shadow' => '0 0 14px rgba(70,71,73,0.1)',
                ];
            case 'inner-shadow':
                return [
                    'border'     => 'none',
                    'background' => 'var(--kb-base-color)',
                    'box-shadow' => 'rgba(44,62,80,0.15) 0px 15px 18px -15px',
                ];
            case 'solid-shadow':
                return [
                    'border'     => '2px solid var(--kb-base-300)',
                    'background' => 'var(--kb-base-color)',
                    'box-shadow' => 'var(--kb-base-200) 10px 10px 0px 0px',
                ];
            case 'active':
                return [
                    'border'     => 'none',
                    'border-top' => '3px solid var(--kb-primary-color)',
                    'background' => 'var(--kb-base-color)',
                    'box-shadow' => '0 1px 2px rgba(70,71,73,0.15)',
                ];
        }
        return array();
    }

}