<?php

namespace KentaCompanion\Extensions;

class Reset {

	public function __construct() {
		add_filter( 'kenta_customizer_call_to_actions', [ $this, 'register_cta' ] );
		add_action( 'customize_register', [ $this, 'customize_register' ] );
		add_action( 'kenta_after_lotta_framework_bootstrap', [ $this, 'customize_register' ] );
		add_action( 'admin_action_kcmp_reset_customizer_options', [ $this, 'reset_customizer_options' ] );
	}

	public function register_cta( $actions ) {
		$actions[] = '#kcmp_reset_customizer_options .button';

		return $actions;
	}

	public function customize_register( $wp_customize ) {
		if ( ! $wp_customize instanceof \WP_Customize_Manager ) {
			$wp_customize = null;
		}

		if ( ! $wp_customize ) {
			return;
		}

		$wp_customize->add_section( new \LottaFramework\Customizer\CallToActionSection( $wp_customize, 'kcmp_reset_customizer_options', array(
			'priority' => 999999,
			'title'    => __( 'Reset Customizer Options', 'kenta' ),
			'link'     => array(
				'url' => esc_url( add_query_arg( array( 'action' => 'kcmp_reset_customizer_options' ), admin_url( 'admin.php' ) ) ),
			)
		) ) );
	}

	public function reset_customizer_options() {

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die(
				'<h1>' . __( 'You need a higher level of permission.', 'kenta-companion' ) . '</h1>' .
				'<p>' . __( 'Sorry, you are not allowed to customize this site.', 'kenta-companion' ) . '</p>',
				403
			);
		}

		\LottaFramework\Facades\CZ::reset();
	}
}