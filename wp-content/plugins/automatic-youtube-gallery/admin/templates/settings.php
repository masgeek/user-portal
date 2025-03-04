<?php

/**
 * Settings Form.
 *
 * @link    https://plugins360.com
 * @since   1.0.0
 *
 * @package Automatic_YouTube_Gallery
 */
?>

<div id="ayg-settings" class="wrap ayg-settings theme-<?php echo esc_attr( $active_theme ); ?> pagination_type-<?php echo esc_attr( $pagination_type ); ?> player_type-<?php echo esc_attr( $player_type ); ?>">
    <h2 class="nav-tab-wrapper">
		<?php
		$settings_url = admin_url( 'admin.php?page=automatic-youtube-gallery-settings' );
		
        foreach ( $this->tabs as $tab => $title ) {
            $class = ( $tab == $active_tab ) ? 'nav-tab nav-tab-active' : 'nav-tab';
            printf( '<a href="%s" class="%s">%s</a>', esc_url( add_query_arg( 'tab', $tab, $settings_url ) ), $class, $title );
        }
        ?>
    </h2>
    
	<?php settings_errors(); ?>
    
	<form method="post" action="options.php"> 
		<?php
        settings_fields( "ayg_{$active_tab}_settings" );
        do_settings_sections( "ayg_{$active_tab}_settings" );
        ?>

        <?php if ( 'general' == $active_tab ) : ?>
            <table id="ayg-table-delete-cache" class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e( 'Delete Cache', 'automatic-youtube-gallery' ); ?></label>
                        </th>
                        <td>
                            <input type="submit" id="ayg-button-delete-cache" class="button-secondary" value="<?php esc_attr_e( 'Delete Cache', 'automatic-youtube-gallery' ); ?>" />
                            <span class="ayg-ajax-status"></span>
                            <p class="description"><?php esc_html_e( 'Delete all of the YouTube API data cached by the plugin.', 'automatic-youtube-gallery' ); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>

        <?php submit_button(); ?>
    </form>
</div>
