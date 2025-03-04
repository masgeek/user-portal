<?php

namespace Wpmoose\WpDismissibleNotice;

class Notices {

	private static $ver = '1.0.0';

	/**
	 * Instances map
	 *
	 * @var array
	 */
	private static $_instances = [];

	/**
	 * Instance id
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Base url
	 *
	 * @var string
	 */
	private $base_url;

	/**
	 * Save all notices
	 *
	 * @var array
	 */
	private $notices = [];

	/**
	 * Construct notice object
	 *
	 * @param $id
	 * @param $base_url
	 */
	private function __construct( $id, $base_url ) {
		$this->id       = $id;
		$this->base_url = trailingslashit( $base_url );

		$this->add_actions();
	}

	/**
	 * Get notice instance
	 *
	 * @param string $id
	 *
	 * @return mixed|Notices
	 */
	public static function instance( $id, $base_url ) {
		if ( ! isset( self::$_instances[ $id ] ) ) {
			self::$_instances[ $id ] = new self( $id, $base_url );
		}

		return self::$_instances[ $id ];
	}

	/**
	 * Hooks
	 */
	private function add_actions() {
		add_action( 'admin_enqueue_scripts', [ $this, '_enqueue_scripts' ] );
		add_action( 'admin_notices', [ $this, '_render_notices' ] );
		add_action( 'admin_init', [ $this, '_ajax_dismiss_notice' ] );
	}

	/**
	 * Enqueue scripts
	 */
	public function _enqueue_scripts() {
		wp_register_script(
			'wpmoose-wp-dismissible-notice',
			$this->asset_url( 'js/notices.js' ),
			[ 'jquery' ],
			self::$ver
		);

		wp_register_style(
			'wpmoose-wp-dismissible-notice',
			$this->asset_url( 'css/notices.css' ),
			[],
			self::$ver
		);

		wp_enqueue_script( 'wpmoose-wp-dismissible-notice' );
		wp_enqueue_style( 'wpmoose-wp-dismissible-notice' );
	}

	/**
	 * Render all notices
	 */
	public function _render_notices() {
		global $current_user;
		$user_id = $current_user->ID;

		foreach ( $this->notices as $id => $notice ) {
			// Dismissed notice
			$dismiss_id = $this->get_dismiss_id( $id );
			if ( get_user_meta( $user_id, $dismiss_id ) ) {
				continue;
			}

			// Render message
			if ( isset( $notice['render'] ) ) {
				$notice['render']( $notice, $id );
			} else {
				$this->_render_notice( $notice, $id );
			}
		}
	}

	/**
	 * Render a notice
	 *
	 * @param $notice
	 */
	public function _render_notice( $notice, $dismiss_id ) {
		$css_classes = "wpmoose-notice notice notice-" . $notice['type'];
		$has_title   = isset( $notice['title'] ) && $notice['title'] !== '';
		if ( $has_title ) {
			$css_classes .= ' has-title';
		}

		$dismiss_url = add_query_arg(
			array(
				'wpmoose_notice_id' => $dismiss_id,
				'wpmoose_scope_id'  => $this->id,
			),
			admin_url()
		);

		?>
        <div data-wpmoose-dismiss-url="<?php echo esc_url( $dismiss_url ) ?>"
             class="<?php echo esc_attr( $css_classes ) ?>">
			<?php if ( $has_title ): ?>
                <label class="wpmoose-notice-title"><?php echo esc_html( $notice['title'] ) ?></label>
			<?php endif; ?>

            <div class="wpmoose-notice-close">
                <i class="dashicons dashicons-no" title="Dismiss"></i>
                <span><?php esc_html_e( 'Dismiss' ); ?></span>
            </div>

            <div class="wpmoose-notice-body">
				<?php echo wp_kses_post( $notice['message'] ) ?>
            </div>
        </div>
		<?php
	}

	/**
	 * For ajax dismiss notice
	 */
	public function _ajax_dismiss_notice() {
		$notice_id = esc_html( $_GET['wpmoose_notice_id'] ?? '' );
		$scope_id  = esc_html( $_GET['wpmoose_scope_id'] ?? '' );

		// Don't handle another notice
		if ( $scope_id !== $this->id ) {
			return;
		}

		if ( ! $this->dismiss_notice( $notice_id ) ) {
			wp_die( '', '', array( 'response' => 404 ) );
		}

		wp_die( '', '', array( 'response' => 200 ) );
	}

	/**
	 * Get dismiss id
	 *
	 * @param $message_id
	 *
	 * @return string
	 */
	public function get_dismiss_id( $message_id ) {
		return 'wpmoose_notice_' . $this->id . '_' . $message_id;
	}

	/**
	 * Get asset file url
	 *
	 * @param string $file
	 *
	 * @return mixed|string
	 */
	public function asset_url( $file = '' ) {
		return $this->base_url . 'assets/' . $file;
	}

	/**
	 * Add a notice
	 *
	 * @param $message
	 * @param $id
	 * @param string $type
	 */
	public function add_notice( $message, $id, $title = '', $type = 'success' ) {
		$this->notices[ $id ] = [
			'message' => $message,
			'title'   => $title,
			'type'    => $type
		];
	}

	/**
	 * Remove notice
	 *
	 * @param $id
	 */
	public function remove_notice( $id ) {
		unset( $this->notices[ $id ] );
	}

	/**
	 * Dismiss a notice
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function dismiss_notice( $id ) {
		if ( isset( $this->notices[ $id ] ) ) {
			global $current_user;
			$user_id = $current_user->ID;

			add_user_meta( $user_id, $this->get_dismiss_id( $id ), 'true', true );

			return true;
		}

		return false;
	}

	/**
	 * Reset notice state
	 *
	 * @param $id
	 */
	public function reset_notice( $id ) {
		if ( isset( $this->notices[ $id ] ) ) {
			global $current_user;
			$user_id = $current_user->ID;

			delete_user_meta( $user_id, $this->get_dismiss_id( $id ), 'true', true );
		}
	}
}