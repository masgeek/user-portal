<?php
/**
 * Template admin/views/dashboard/new-feature-notice.php
 *
 * @package Forminator
 */

$user      = wp_get_current_user();
$banner_1x = forminator_plugin_url() . 'assets/images/Feature_highlight.png';
$banner_2x = forminator_plugin_url() . 'assets/images/Feature_highlight@2x.png';
?>

<div class="sui-modal sui-modal-md">

	<div
		role="dialog"
		id="forminator-new-feature"
		class="sui-modal-content"
		aria-live="polite"
		aria-modal="true"
		aria-labelledby="forminator-new-feature__title"
	>

		<div class="sui-box forminator-feature-modal" data-prop="forminator_dismiss_feature_1380" data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_dismiss_notification' ) ); ?>">

			<div class="sui-box-header sui-flatten sui-content-center">

				<figure class="sui-box-banner" aria-hidden="true">
					<img
						src="<?php echo esc_url( $banner_1x ); ?>"
						srcset="<?php echo esc_url( $banner_1x ); ?> 1x, <?php echo esc_url( $banner_2x ); ?> 2x"
						alt=""
					/>
				</figure>

				<button class="sui-button-icon sui-button-white sui-button-float--right forminator-dismiss-new-feature" data-type="dismiss" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog.', 'forminator' ); ?></span>
				</button>

				<h3 class="sui-box-title sui-lg" style="overflow: initial; white-space: initial; text-overflow: initial;">
					<?php esc_html_e( 'New: Stripe Optimized Checkout Suite (OCS)', 'forminator' ); ?>
				</h3>

				<p class="sui-description">
					<?php
					printf(
						/* translators: 1. Admin name */
						esc_html__( 'Hello, %s! we’ve made important improvements to the Forminator Stripe field and integrated a new Dynamic Payment Method, Payment Element. You can now enjoy enhanced security and support for multiple payment methods, such as PayPal, GPay, Apple Pay, and more.', 'forminator' ),
						esc_html( ucfirst( $user->display_name ) ),
					);
					?>
				</p>
				<p></p>
				<p class="sui-description">
					<?php
					printf(
						/* translators: 1. Open a tag. 2, Close a tag. */
						esc_html__( 'If you\'ve been using the old Stripe setup in your form(s), migrate to the new Stripe field today to ensure uninterrupted transactions. %1$sLearn more%2$s.', 'forminator' ),
						'<a href="https://wpmudev.com/docs/wpmu-dev-plugins/forminator/#stripe-field" target="_blank">',
						'</a>'
					);
					?>
				</p>

			</div>

			<div class="sui-box-footer sui-flatten sui-content-center">

				<button class="sui-button sui-button-ghost forminator-dismiss-new-feature" data-modal-close>
					<?php esc_html_e( 'Close', 'forminator' ); ?>
				</button>
				<button data-link="<?php echo esc_url( add_query_arg( array( 'page' => 'forminator-cform' ), admin_url( 'admin.php' ) ) ); ?>" class="sui-button sui-button-blue forminator-dismiss-new-feature" data-modal-close>
					<?php esc_html_e( 'Migrate Forms', 'forminator' ); ?>
				</button>

			</div>

		</div>

	</div>

</div>

<script type="text/javascript">
	jQuery('#forminator-new-feature .forminator-dismiss-new-feature').on('click', function (e) {
	e.preventDefault()

	var $notice = jQuery(e.currentTarget).closest('.forminator-feature-modal'),
		ajaxUrl = '<?php echo esc_url( forminator_ajax_url() ); ?>',
		$self   = jQuery(this),
		dataType = jQuery(this).data('type'),
		ajaxData = {
		action: 'forminator_dismiss_notification',
		prop: $notice.data('prop'),
		_ajax_nonce: $notice.data('nonce')
		}

	if ( 'save' === dataType ) {
		ajaxData['usage_value'] = jQuery('#forminator-new-feature-toggle').is(':checked')
	}

	jQuery.post(ajaxUrl, ajaxData)
		.always(function () {
			$notice.hide();
			let link = $self.data('link');
			if ( link ) {
				location.href = link;
			}
		})
	})
</script>
