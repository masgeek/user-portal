<?php

namespace KentaCompanion\Extensions;

use LottaFramework\Customizer\Controls\Background;
use LottaFramework\Customizer\Controls\Toggle;
use LottaFramework\Facades\AsyncCss;
use LottaFramework\Facades\Css;
use LottaFramework\Facades\CZ;
class Builder {
    public function __construct() {
        add_action( 'kenta_header_builder_initialized', [$this, 'header_builder'] );
        add_action( 'kenta_footer_builder_initialized', [$this, 'footer_builder'] );
    }

    public function header_builder( $builder ) {
    }

    public function footer_builder( $builder ) {
    }

}
