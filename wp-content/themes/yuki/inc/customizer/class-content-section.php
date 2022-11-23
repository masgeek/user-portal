<?php

/**
 * Content customizer section
 *
 * @package Yuki
 */
use  LottaFramework\Customizer\Controls\Border ;
use  LottaFramework\Customizer\Controls\ColorPicker ;
use  LottaFramework\Customizer\Controls\Placeholder ;
use  LottaFramework\Customizer\Controls\Section ;
use  LottaFramework\Customizer\Controls\Separator ;
use  LottaFramework\Customizer\Controls\Spacing ;
use  LottaFramework\Customizer\Controls\Typography ;
use  LottaFramework\Customizer\Section as CustomizerSection ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}

if ( !class_exists( 'Yuki_Content_Section' ) ) {
    class Yuki_Content_Section extends CustomizerSection
    {
        use  Yuki_Button_Controls ;
        /**
         * {@inheritDoc}
         */
        public function getControls()
        {
            return [
                ( new Section( 'yuki_content_colors' ) )->setLabel( __( 'Colors', 'yuki' ) )->setControls( $this->getColorsControls() ),
                ( new Section( 'yuki_content_typography' ) )->setLabel( __( 'Typography', 'yuki' ) )->setControls( $this->getTypographyControls() ),
                ( new Section( 'yuki_content_buttons' ) )->setLabel( __( 'Buttons', 'yuki' ) )->setControls( $this->getButtonStyleControls( 'yuki_content_buttons_', [
                'selective-refresh' => true,
                'button-selector'   => [
                '.yuki-article-content .wp-block-button',
                '.yuki-article-content button',
                '.yuki-article-content [type="submit"]',
                '.yuki-comments-area [type="submit"]'
            ],
            ] ) ),
                ( new Section( 'yuki_content_comments' ) )->setLabel( __( 'Comments Area', 'yuki' ) )->setControls( $this->getCommentsControls() )
            ];
        }
        
        /**
         * @return array
         */
        protected function getTypographyControls()
        {
            $fonts = [
                'base_typography'     => [
                'label'   => __( 'Base Typography', 'yuki' ),
                'default' => [
                'family'     => 'inherit',
                'fontSize'   => '1rem',
                'variant'    => '400',
                'lineHeight' => '1.75',
            ],
            ],
                'drop_cap_typography' => [
                'label'   => __( 'Drop Cap', 'yuki' ),
                'default' => [
                'family'        => 'serif',
                'fontSize'      => '5rem',
                'variant'       => '700',
                'lineHeight'    => '1',
                'textTransform' => 'uppercase',
            ],
            ],
            ];
            $controls = [];
            foreach ( $fonts as $item => $font ) {
                $controls[] = ( new Typography( 'yuki_content_' . $item ) )->setLabel( $font['label'] )->bindSelectiveRefresh( 'yuki-global-selective-css' )->setDefaultValue( $font['default'] );
            }
            if ( yuki_fs()->is_not_paying() ) {
                $controls[] = yuki_upsell_info_control( __( 'More typography options in %sPro Version%s', 'yuki' ) )->showBackground();
            }
            return $controls;
        }
        
        /**
         * @return array
         */
        protected function getColorsControls()
        {
            $colors = [
                'base_color'     => [
                'label'  => __( 'Base Color', 'yuki' ),
                'colors' => [
                'initial' => 'var(--yuki-accent-color)',
            ],
            ],
                'headings_color' => [
                'label'  => __( 'All Headings Color (H1 - H6)', 'yuki' ),
                'colors' => [
                'initial' => 'var(--yuki-accent-active)',
            ],
            ],
            ];
            $controls = [];
            foreach ( $colors as $item => $color ) {
                $picker = new ColorPicker( 'yuki_content_' . $item );
                $picker->bindSelectiveRefresh( 'yuki-global-selective-css' );
                $picker->setLabel( $color['label'] );
                foreach ( $color['colors'] as $id => $value ) {
                    $picker->addColor( $id, ucfirst( $id ), $value );
                }
                $controls[] = $picker;
            }
            if ( yuki_fs()->is_not_paying() ) {
                $controls[] = yuki_upsell_info_control( __( 'More color options in %sPro Version%s', 'yuki' ) )->showBackground();
            }
            return $controls;
        }
        
        /**
         * @return array
         */
        protected function getCommentsControls()
        {
            $controls = [];
            $controls = [ ( new Placeholder( 'yuki_content_comments_typography' ) )->setDefaultValue( [
                'family'     => 'inherit',
                'fontSize'   => '0.85rem',
                'variant'    => '400',
                'lineHeight' => '1.5em',
            ] ), ( new Placeholder( 'yuki_content_comments_text_color' ) )->addColor( 'initial', 'var(--yuki-accent-active)' )->addColor( 'hover', 'var(--yuki-primary-color)' ), ( new Placeholder( 'yuki_content_comments_form_color' ) )->addColor( 'background', 'var(--yuki-base-color)' )->addColor( 'border', 'var(--yuki-base-200)' )->addColor( 'active', 'var(--yuki-primary-color)' ) ];
            $controls = array_merge( $controls, [
                ( new Border( 'yuki_content_comments_border_top' ) )->setLabel( __( 'Top Border', 'yuki' ) )->enableResponsive()->bindSelectiveRefresh( 'yuki-global-selective-css' )->displayBlock()->setDefaultBorder( 1, 'none', 'var(--yuki-base-200)' ),
                new Separator(),
                ( new Border( 'yuki_content_comments_border_bottom' ) )->setLabel( __( 'Bottom Border', 'yuki' ) )->enableResponsive()->bindSelectiveRefresh( 'yuki-global-selective-css' )->displayBlock()->setDefaultBorder( 1, 'none', 'var(--yuki-base-200)' ),
                new Separator(),
                ( new Spacing( 'yuki_content_comments_padding' ) )->setLabel( __( 'Padding', 'yuki' ) )->enableResponsive()->bindSelectiveRefresh( 'yuki-global-selective-css' )->setDisabled( [ 'left', 'right' ] )->setSpacing( [
                'linked' => true,
            ] ),
                ( new Spacing( 'yuki_content_comments_margin' ) )->setLabel( __( 'Margin', 'yuki' ) )->enableResponsive()->bindSelectiveRefresh( 'yuki-global-selective-css' )->setDisabled( [ 'left', 'right' ] )->setSpacing( [
                'linked' => true,
            ] )
            ] );
            if ( yuki_fs()->is_not_paying() ) {
                $controls[] = yuki_upsell_info_control( __( 'More options in %sPro Version%s', 'yuki' ) )->showBackground();
            }
            return $controls;
        }
    
    }
}