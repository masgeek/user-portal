<?php

namespace KentaCompanion\Extensions;

use LottaFramework\Customizer\Controls\FileUploader;
use LottaFramework\Customizer\Controls\Section;
use LottaFramework\Customizer\Controls\Select;
use LottaFramework\Customizer\Controls\Text;
class CustomFonts {
    public function __construct() {
        add_filter( 'kenta_global_section_controls', [$this, 'inject_controls'], 9 );
    }

    /**
     * Inject controls to gloabl section
     *
     * @param $controls
     *
     * @return mixed
     */
    public function inject_controls( $controls ) {
        $custom_fonts_controls = [kcmp_upsell_info_control( __( 'Add your custom fonts in %sPro Version%s', 'kenta-companion' ) )];
        $custom_fonts_controls[] = kcmp_docs_control( __( '%sRead documentation for custom fonts%s', 'kenta-companion' ), 'https://kentatheme.com/docs/kenta-theme/general/custom-fonts/', 'kcmp_custom_fonts_doc' );
        $controls[] = ( new Section('kenta_global_custom_fonts') )->setLabel( __( 'Custom Fonts', 'kenta-companion' ) )->setControls( $custom_fonts_controls );
        return $controls;
    }

}
