<?php

/**
 * Frontend blocks dynamic assets utils
 *
 * @package Kenta Blocks
 */
namespace KentaBlocks;

class Assets {
    /**
     * Member Variable
     *
     * @var Css
     */
    private static $instance;

    /**
     * Constructor
     */
    public function __construct() {
        $frontend_priority = apply_filters( 'kb/frontend_scripts_priority', 9999 );
        $admin_priority = apply_filters( 'kb/admin_scripts_priority', 9999 );
        add_action( 'init', array($this, 'register') );
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'), $frontend_priority );
        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'), $admin_priority );
        add_action( 'save_post', 'kenta_blocks_regenerate_assets' );
        add_action( 'save_post_wp_block', 'kenta_blocks_regenerate_assets' );
    }

    /**
     *  Initiator
     */
    public static function get_instance() {
        if ( !isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register plugin assets
     */
    public function register() {
        $suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ? '' : '.min' );
        // Vendor
        wp_register_style(
            'fontawesome',
            KENTA_BLOCKS_PLUGIN_URL . 'assets/fontawesome/css/all.min.css',
            array(),
            KENTA_BLOCKS_VERSION
        );
        wp_register_script(
            'slick',
            KENTA_BLOCKS_PLUGIN_URL . 'assets/vendor/slick/slick.min.js',
            array('jquery'),
            KENTA_BLOCKS_VERSION,
            true
        );
        wp_register_style(
            'slick',
            KENTA_BLOCKS_PLUGIN_URL . 'assets/vendor/slick/slick.css',
            array(),
            KENTA_BLOCKS_VERSION
        );
        wp_register_script(
            'particles.js',
            KENTA_BLOCKS_PLUGIN_URL . 'assets/vendor/particles/tsparticles.bundle.min.js',
            array(),
            KENTA_BLOCKS_VERSION,
            true
        );
        // Register blocks style
        wp_register_style(
            'kenta-blocks-style',
            KENTA_BLOCKS_PLUGIN_URL . 'dist/blocks.style' . $suffix . '.css',
            array('fontawesome'),
            KENTA_BLOCKS_VERSION
        );
        // Register editor style
        wp_register_style(
            'kenta-blocks-editor-style',
            KENTA_BLOCKS_PLUGIN_URL . 'dist/blocks.editor' . $suffix . '.css',
            array('wp-edit-blocks', 'fontawesome'),
            KENTA_BLOCKS_VERSION
        );
        $script = 'blocks';
        $wcScript = 'wc-blocks';
        $wc_script_asset = (require KENTA_BLOCKS_PLUGIN_PATH . "dist/{$wcScript}.asset.php");
        $wc_script_dependencies = ( isset( $wc_script_asset['dependencies'] ) ? $wc_script_asset['dependencies'] : array() );
        $script_asset = (require KENTA_BLOCKS_PLUGIN_PATH . "dist/{$script}.asset.php");
        $script_dependencies = ( isset( $script_asset['dependencies'] ) ? $script_asset['dependencies'] : array() );
        $frontend_asset = (require KENTA_BLOCKS_PLUGIN_PATH . "dist/frontend.asset.php");
        $frontend_dependencies = ( isset( $frontend_asset['dependencies'] ) ? $frontend_asset['dependencies'] : array() );
        wp_register_script(
            'kenta-blocks-wc-script',
            KENTA_BLOCKS_PLUGIN_URL . "dist/{$wcScript}{$suffix}.js",
            $wc_script_dependencies,
            KENTA_BLOCKS_VERSION,
            true
        );
        // Register scripts
        wp_register_script(
            'kenta-blocks-editor-script',
            KENTA_BLOCKS_PLUGIN_URL . "dist/{$script}{$suffix}.js",
            $script_dependencies,
            KENTA_BLOCKS_VERSION,
            true
        );
        wp_register_script(
            'kenta-blocks-frontend-script',
            KENTA_BLOCKS_PLUGIN_URL . "dist/frontend{$suffix}.js",
            $frontend_dependencies,
            KENTA_BLOCKS_VERSION,
            true
        );
        $blocks = kenta_blocks_all( 'metadata' );
        $localize_script = apply_filters( 'kb/js_localize', array(
            'upsell'                        => 'https://kentatheme.com/pricing/',
            'plan'                          => ( kb_fs()->can_use_premium_code() ? 'premium' : 'free' ),
            'debug'                         => defined( 'KENTA_BLOCKS_DEBUG' ) && KENTA_BLOCKS_DEBUG,
            'pattern_placeholder_image'     => KENTA_BLOCKS_PLUGIN_URL . 'assets/images/pattern-placeholder.jpg',
            'iconsLibrary'                  => \KentaBlocks\IconsManager::allLibraries(),
            'isWP59OrAbove'                 => is_wp_version_compatible( '5.9' ),
            'isWooCommerceActive'           => KENTA_BLOCKS_WOOCOMMERCE_ACTIVE,
            'enableEditorResponsivePreview' => kenta_blocks_setting()->value( 'kb_editor_responsive_preview' ) === 'yes',
            'excerptLength'                 => apply_filters( 'excerpt_length', 55 ),
            'excerptMore'                   => apply_filters( 'excerpt_more', ' ' . '[&hellip;]' ),
            'magazineLayouts'               => \KentaBlocks\Blocks\Magazine::all(),
            'breakpoints'                   => array(
                'desktop' => kenta_blocks_css()->desktop(),
                'tablet'  => kenta_blocks_css()->tablet(),
                'mobile'  => kenta_blocks_css()->mobile(),
            ),
            'blocks'                        => $blocks,
            'colorPicker'                   => array(
                'swatches' => $this->get_color_swatches(),
            ),
            'gradientPicker'                => array(
                'swatches' => $this->get_gradient_swatches(),
            ),
            'fonts'                         => array(
                'system' => Fonts::system(),
                'google' => Fonts::google(),
            ),
            'shapes'                        => kenta_blocks_get_shapes(),
        ) );
        wp_localize_script( 'kenta-blocks-editor-script', 'KentaBlocks', $localize_script );
    }

    /**
     * ColorPicker swatches
     *
     * @return mixed|void
     */
    protected function get_color_swatches() {
        $swatches = array(
            'var(--kb-primary-color)',
            'var(--kb-primary-active)',
            'var(--kb-accent-color)',
            'var(--kb-accent-active)',
            'var(--kb-base-300)',
            'var(--kb-base-200)',
            'var(--kb-base-100)',
            'var(--kb-base-color)',
            Css::INITIAL_VALUE
        );
        return apply_filters( 'kb/color_swatches', $swatches );
    }

    /**
     * GradientPicker swatches
     *
     * @return mixed|void
     */
    protected function get_gradient_swatches() {
        $swatches = array(
            array(
                'name'     => 'Vivid cyan blue to vivid purple',
                'gradient' => 'linear-gradient(135deg,rgba(6,147,227,1) 0%,rgb(155,81,224) 100%)',
                'slug'     => 'vivid-cyan-blue-to-vivid-purple',
            ),
            array(
                'name'     => 'Light green cyan to vivid green cyan',
                'gradient' => 'linear-gradient(135deg,rgb(122,220,180) 0%,rgb(0,208,130) 100%)',
                'slug'     => 'light-green-cyan-to-vivid-green-cyan',
            ),
            array(
                'name'     => 'Luminous vivid amber to luminous vivid orange',
                'gradient' => 'linear-gradient(135deg,rgba(252,185,0,1) 0%,rgba(255,105,0,1) 100%)',
                'slug'     => 'luminous-vivid-amber-to-luminous-vivid-orange',
            ),
            array(
                'name'     => 'Luminous vivid orange to vivid red',
                'gradient' => 'linear-gradient(135deg,rgba(255,105,0,1) 0%,rgb(207,46,46) 100%)',
                'slug'     => 'luminous-vivid-orange-to-vivid-red',
            ),
            array(
                'name'     => 'Cool to warm spectrum',
                'gradient' => 'linear-gradient(135deg,rgb(74,234,220) 0%,rgb(151,120,209) 20%,rgb(207,42,186) 40%,rgb(238,44,130) 60%,rgb(251,105,98) 80%,rgb(254,248,76) 100%)',
                'slug'     => 'cool-to-warm-spectrum',
            ),
            array(
                'name'     => 'Blush light purple',
                'gradient' => 'linear-gradient(135deg,rgb(255,206,236) 0%,rgb(152,150,240) 100%)',
                'slug'     => 'blush-light-purple',
            ),
            array(
                'name'     => 'Blush bordeaux',
                'gradient' => 'linear-gradient(135deg,rgb(254,205,165) 0%,rgb(254,45,45) 50%,rgb(107,0,62) 100%)',
                'slug'     => 'blush-bordeaux',
            ),
            array(
                'name'     => 'Luminous dusk',
                'gradient' => 'linear-gradient(135deg,rgb(255,203,112) 0%,rgb(199,81,192) 50%,rgb(65,88,208) 100%)',
                'slug'     => 'luminous-dusk',
            ),
            array(
                'name'     => 'Pale ocean',
                'gradient' => 'linear-gradient(135deg,rgb(255,245,203) 0%,rgb(182,227,212) 50%,rgb(51,167,181) 100%)',
                'slug'     => 'pale-ocean',
            ),
            array(
                'name'     => 'Electric grass',
                'gradient' => 'linear-gradient(135deg,rgb(202,248,128) 0%,rgb(113,206,126) 100%)',
                'slug'     => 'electric-grass',
            ),
            array(
                'name'     => 'Midnight',
                'gradient' => 'linear-gradient(135deg,rgb(2,3,129) 0%,rgb(40,116,252) 100%)',
                'slug'     => 'midnight',
            ),
            array(
                'name'     => 'Juicy Peach',
                'gradient' => 'linear-gradient(to right, #ffecd2 0%, #fcb69f 100%)',
                'slug'     => 'juicy-peach',
            ),
            array(
                'name'     => 'Young Passion',
                'gradient' => 'linear-gradient(to right, #ff8177 0%, #ff867a 0%, #ff8c7f 21%, #f99185 52%, #cf556c 78%, #b12a5b 100%)',
                'slug'     => 'young-passion',
            ),
            array(
                'name'     => 'True Sunset',
                'gradient' => 'linear-gradient(to right, #fa709a 0%, #fee140 100%)',
                'slug'     => 'true-sunset',
            ),
            array(
                'name'     => 'Morpheus Den',
                'gradient' => 'linear-gradient(to top, #30cfd0 0%, #330867 100%)',
                'slug'     => 'morpheus-den',
            ),
            array(
                'name'     => 'Plum Plate',
                'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'slug'     => 'plum-plate',
            ),
            array(
                'name'     => 'Aqua Splash',
                'gradient' => 'linear-gradient(15deg, #13547a 0%, #80d0c7 100%)',
                'slug'     => 'aqua-splash',
            ),
            array(
                'name'     => 'Love Kiss',
                'gradient' => 'linear-gradient(to top, #ff0844 0%, #ffb199 100%)',
                'slug'     => 'love-kiss',
            ),
            array(
                'name'     => 'New Retrowave',
                'gradient' => 'linear-gradient(to top, #3b41c5 0%, #a981bb 49%, #ffc8a9 100%)',
                'slug'     => 'new-retrowave',
            ),
            array(
                'name'     => 'Plum Bath',
                'gradient' => 'linear-gradient(to top, #cc208e 0%, #6713d2 100%)',
                'slug'     => 'plum-bath',
            ),
            array(
                'name'     => 'High Flight',
                'gradient' => 'linear-gradient(to right, #0acffe 0%, #495aff 100%)',
                'slug'     => 'high-flight',
            ),
            array(
                'name'     => 'Teen Party',
                'gradient' => 'linear-gradient(-225deg, #FF057C 0%, #8D0B93 50%, #321575 100%)',
                'slug'     => 'teen-party',
            ),
            array(
                'name'     => 'Fabled Sunset',
                'gradient' => 'linear-gradient(-225deg, #231557 0%, #44107A 29%, #FF1361 67%, #FFF800 100%)',
                'slug'     => 'fabled-sunset',
            ),
            array(
                'name'     => 'Arielle Smile',
                'gradient' => 'radial-gradient(circle 248px at center, #16d9e3 0%, #30c7ec 47%, #46aef7 100%)',
                'slug'     => 'arielle-smile',
            ),
            array(
                'name'     => 'Itmeo Branding',
                'gradient' => 'linear-gradient(180deg, #2af598 0%, #009efd 100%)',
                'slug'     => 'itmeo-branding',
            ),
            array(
                'name'     => 'Deep Blue',
                'gradient' => 'linear-gradient(to right, #6a11cb 0%, #2575fc 100%)',
                'slug'     => 'deep-blue',
            ),
            array(
                'name'     => 'Strong Bliss',
                'gradient' => 'linear-gradient(to right, #f78ca0 0%, #f9748f 19%, #fd868c 60%, #fe9a8b 100%)',
                'slug'     => 'strong-bliss',
            ),
            array(
                'name'     => 'Sweet Period',
                'gradient' => 'linear-gradient(to top, #3f51b1 0%, #5a55ae 13%, #7b5fac 25%, #8f6aae 38%, #a86aa4 50%, #cc6b8e 62%, #f18271 75%, #f3a469 87%, #f7c978 100%)',
                'slug'     => 'sweet-period',
            ),
            array(
                'name'     => 'Purple Division',
                'gradient' => 'linear-gradient(to top, #7028e4 0%, #e5b2ca 100%)',
                'slug'     => 'purple-division',
            ),
            array(
                'name'     => 'Cold Evening',
                'gradient' => 'linear-gradient(to top, #0c3483 0%, #a2b6df 100%, #6b8cce 100%, #a2b6df 100%)',
                'slug'     => 'cold-evening',
            ),
            array(
                'name'     => 'Mountain Rock',
                'gradient' => 'linear-gradient(to right, #868f96 0%, #596164 100%)',
                'slug'     => 'mountain-rock',
            ),
            array(
                'name'     => 'Desert Hump',
                'gradient' => 'linear-gradient(to top, #c79081 0%, #dfa579 100%)',
                'slug'     => 'desert-hump',
            ),
            array(
                'name'     => 'Eternal Constance',
                'gradient' => 'linear-gradient(to top, #09203f 0%, #537895 100%)',
                'slug'     => 'ethernal-constance',
            ),
            array(
                'name'     => 'Happy Memories',
                'gradient' => 'linear-gradient(-60deg, #ff5858 0%, #f09819 100%)',
                'slug'     => 'happy-memories',
            ),
            array(
                'name'     => 'Grown Early',
                'gradient' => 'linear-gradient(to top, #0ba360 0%, #3cba92 100%)',
                'slug'     => 'grown-early',
            ),
            array(
                'name'     => 'Morning Salad',
                'gradient' => 'linear-gradient(-225deg, #B7F8DB 0%, #50A7C2 100%)',
                'slug'     => 'morning-salad',
            ),
            array(
                'name'     => 'Night Call',
                'gradient' => 'linear-gradient(-225deg, #AC32E4 0%, #7918F2 48%, #4801FF 100%)',
                'slug'     => 'night-call',
            ),
            array(
                'name'     => 'Mind Crawl',
                'gradient' => 'linear-gradient(-225deg, #473B7B 0%, #3584A7 51%, #30D2BE 100%)',
                'slug'     => 'mind-crawl',
            ),
            array(
                'name'     => 'Angel Care',
                'gradient' => 'linear-gradient(-225deg, #FFE29F 0%, #FFA99F 48%, #FF719A 100%)',
                'slug'     => 'angel-care',
            ),
            array(
                'name'     => 'Juicy Cake',
                'gradient' => 'linear-gradient(to top, #e14fad 0%, #f9d423 100%)',
                'slug'     => 'juicy-cake',
            ),
            array(
                'name'     => 'Rich Metal',
                'gradient' => 'linear-gradient(to right, #d7d2cc 0%, #304352 100%)',
                'slug'     => 'rich-metal',
            ),
            array(
                'name'     => 'Mole Hall',
                'gradient' => 'linear-gradient(-20deg, #616161 0%, #9bc5c3 100%)',
                'slug'     => 'mole-hall',
            ),
            array(
                'name'     => 'Cloudy Knoxville',
                'gradient' => 'linear-gradient(120deg, #fdfbfb 0%, #ebedee 100%)',
                'slug'     => 'cloudy-knoxville',
            ),
            array(
                'name'     => 'Very light gray to cyan bluish gray',
                'gradient' => 'linear-gradient(135deg,rgb(238,238,238) 0%,rgb(169,184,195) 100%)',
                'slug'     => 'very-light-gray-to-cyan-bluish-gray',
            ),
            array(
                'name'     => 'Soft Grass',
                'gradient' => 'linear-gradient(to top, #c1dfc4 0%, #deecdd 100%)',
                'slug'     => 'soft-grass',
            ),
            array(
                'name'     => 'Saint Petersburg',
                'gradient' => 'linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%)',
                'slug'     => 'saint-petersburg',
            ),
            array(
                'name'     => 'Everlasting Sky',
                'gradient' => 'linear-gradient(135deg, #fdfcfb 0%, #e2d1c3 100%)',
                'slug'     => 'everlasting-sky',
            ),
            array(
                'name'     => 'Kind Steel',
                'gradient' => 'linear-gradient(-20deg, #e9defa 0%, #fbfcdb 100%)',
                'slug'     => 'kind-steel',
            ),
            array(
                'name'     => 'Over Sun',
                'gradient' => 'linear-gradient(60deg, #abecd6 0%, #fbed96 100%)',
                'slug'     => 'over-sun',
            ),
            array(
                'name'     => 'Premium White',
                'gradient' => 'linear-gradient(to top, #d5d4d0 0%, #d5d4d0 1%, #eeeeec 31%, #efeeec 75%, #e9e9e7 100%)',
                'slug'     => 'premium-white',
            ),
            array(
                'name'     => 'Clean Mirror',
                'gradient' => 'linear-gradient(45deg, #93a5cf 0%, #e4efe9 100%)',
                'slug'     => 'clean-mirror',
            ),
            array(
                'name'     => 'Wild Apple',
                'gradient' => 'linear-gradient(to top, #d299c2 0%, #fef9d7 100%)',
                'slug'     => 'wild-apple',
            ),
            array(
                'name'     => 'Snow Again',
                'gradient' => 'linear-gradient(to top, #e6e9f0 0%, #eef1f5 100%)',
                'slug'     => 'snow-again',
            ),
            array(
                'name'     => 'Confident Cloud',
                'gradient' => 'linear-gradient(to top, #dad4ec 0%, #dad4ec 1%, #f3e7e9 100%)',
                'slug'     => 'confident-cloud',
            ),
            array(
                'name'     => 'Glass Water',
                'gradient' => 'linear-gradient(to top, #dfe9f3 0%, white 100%)',
                'slug'     => 'glass-water',
            ),
            array(
                'name'     => 'Perfect White',
                'gradient' => 'linear-gradient(-225deg, #E3FDF5 0%, #FFE6FA 100%)',
                'slug'     => 'perfect-white',
            )
        );
        return apply_filters( 'kb/gradient_swatches', $swatches );
    }

    /**
     * Enqueue frontend scripts
     *
     * @return void
     */
    public function enqueue_frontend_scripts() {
        //Get sidebar blocks dependencies
        list( $sidebar_sc, $sidebar_st ) = $this->get_blocks_dependencies( kb_sidebar_blocks() );
        // Sidebar assets
        wp_register_style(
            'kenta-blocks-frontend-sidebar-styles',
            false,
            $sidebar_st,
            KENTA_BLOCKS_VERSION
        );
        wp_enqueue_style( 'kenta-blocks-frontend-sidebar-styles' );
        wp_add_inline_style( 'kenta-blocks-frontend-sidebar-styles', kenta_blocks_css()->dynamicSidebarCssRaw() );
        wp_register_script(
            'kenta-blocks-frontend-sidebar-script',
            false,
            $sidebar_sc,
            KENTA_BLOCKS_VERSION,
            true
        );
        wp_enqueue_script( 'kenta-blocks-frontend-sidebar-script' );
        wp_add_inline_script( 'kenta-blocks-frontend-sidebar-script', kenta_blocks_script()->dynamicSidebarScriptRaw() );
        //Get post blocks dependencies
        list( $post_sc, $post_st ) = $this->get_blocks_dependencies( kb_post_blocks() );
        // Dynamic inline mode
        wp_register_style(
            'kenta-blocks-frontend-styles',
            false,
            $post_st,
            KENTA_BLOCKS_VERSION
        );
        wp_enqueue_style( 'kenta-blocks-frontend-styles' );
        wp_add_inline_style( 'kenta-blocks-frontend-styles', kenta_blocks_css()->dynamicCssRaw() );
        Fonts::enqueue_scripts( 'kenta-blocks-fonts' );
        wp_register_script(
            'kenta-blocks-frontend-inline-script',
            false,
            $post_sc,
            KENTA_BLOCKS_VERSION,
            true
        );
        wp_enqueue_script( 'kenta-blocks-frontend-inline-script' );
        wp_add_inline_script( 'kenta-blocks-frontend-inline-script', kenta_blocks_script()->dynamicScriptsRaw() );
    }

    /**
     * @param $blocks
     * @param array $scripts
     * @param array $styles
     *
     * @return array|array[]
     */
    protected function get_blocks_dependencies( $blocks, $scripts = array(), $styles = array() ) {
        if ( !is_array( $blocks ) ) {
            return [$scripts, $styles];
        }
        foreach ( $blocks as $block ) {
            if ( is_array( $block ) ) {
                $name = $block['blockName'];
                if ( '' === $name ) {
                    continue;
                }
                if ( 'core/block' === $name ) {
                    $id = ( isset( $block['attrs']['ref'] ) ? $block['attrs']['ref'] : 0 );
                    if ( $id ) {
                        $content = get_post_field( 'post_content', $id );
                        $reusable_blocks = kenta_blocks_parse_content( $content );
                        list( $sc, $st ) = $this->get_blocks_dependencies( $reusable_blocks );
                        $scripts = array_merge( $scripts, $sc );
                        $styles = array_merge( $styles, $st );
                    }
                } else {
                    $kenta_blocks = kenta_blocks_all();
                    if ( isset( $kenta_blocks[$name] ) && isset( $kenta_blocks[$name]['dependencies'] ) ) {
                        if ( isset( $block['attrs'] ) && isset( $block['attrs']['blockID'] ) ) {
                            list( $sc, $st ) = $kenta_blocks[$name]['dependencies']( $block );
                            $scripts = array_merge( $scripts, $sc );
                            $styles = array_merge( $styles, $st );
                        }
                    }
                }
                if ( isset( $block['innerBlocks'] ) ) {
                    list( $sc, $st ) = $this->get_blocks_dependencies( $block['innerBlocks'] );
                    $scripts = array_merge( $scripts, $sc );
                    $styles = array_merge( $styles, $st );
                }
            }
        }
        return [$scripts, $styles];
    }

    /**
     * Enqueue admin scripts
     *
     * @return void
     */
    public function enqueue_admin_scripts() {
        wp_register_style(
            'kenta-blocks-admin-inline',
            false,
            array(),
            KENTA_BLOCKS_VERSION
        );
        wp_enqueue_style( 'kenta-blocks-admin-inline' );
        wp_add_inline_style( 'kenta-blocks-admin-inline', kenta_blocks_css()->parse( kenta_blocks_css()->vars() ) );
        $suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ? '' : '.min' );
        $screen = get_current_screen();
        if ( $screen->base !== 'toplevel_page_kenta-blocks' ) {
            return;
        }
        $script_asset = (require KENTA_BLOCKS_PLUGIN_PATH . 'dist/admin.asset.php');
        $script_dependencies = ( isset( $script_asset['dependencies'] ) ? $script_asset['dependencies'] : array() );
        wp_enqueue_style(
            'kenta-blocks-admin-style',
            KENTA_BLOCKS_PLUGIN_URL . 'dist/admin' . $suffix . '.css',
            [],
            KENTA_BLOCKS_VERSION
        );
        wp_enqueue_script(
            'kenta-blocks-admin-script',
            KENTA_BLOCKS_PLUGIN_URL . 'dist/admin' . $suffix . '.js',
            $script_dependencies,
            KENTA_BLOCKS_VERSION
        );
        wp_localize_script( 'kenta-blocks-admin-script', 'KentaBlocks', apply_filters( 'kb/js_admin_localize', array(
            'upsell'        => 'https://kentatheme.com/pricing/',
            'plan'          => ( kb_fs()->can_use_premium_code() ? 'premium' : 'free' ),
            'debug'         => defined( 'KENTA_BLOCKS_DEBUG' ) && KENTA_BLOCKS_DEBUG,
            'isWP59OrAbove' => is_wp_version_compatible( '5.9' ),
        ) ) );
    }

}
