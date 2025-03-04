<?php

/**
 * Plugin Dashboard.
 *
 * @link    https://plugins360.com
 * @since   1.3.0
 *
 * @package Automatic_YouTube_Gallery
 */
?>

<div id="ayg-dashboard" class="wrap about-wrap full-width-layout ayg-dashboard">
  <h1><?php esc_html_e( 'Welcome to "Automatic YouTube Gallery"', 'automatic-youtube-gallery' ); ?></h1>

  <p class="about-text">
    <?php esc_html_e( 'Create responsive, modern & dynamic video galleries by simply adding a YouTube USERNAME, CHANNEL, PLAYLIST, SEARCH KEYWORDS, or a custom list of YouTube URLs.', 'automatic-youtube-gallery' ); ?>
  </p>

  <div class="wp-badge"><?php printf( esc_html__( 'Version %s', 'automatic-youtube-gallery' ), AYG_VERSION ); ?></div>

  <h2 class="nav-tab-wrapper wp-clearfix">
    <?php
    $plugin_url = admin_url( 'admin.php?page=automatic-youtube-gallery' );

    foreach ( $tabs as $tab => $title ) {
      $class = ( $tab == $active_tab ) ? 'nav-tab nav-tab-active' : 'nav-tab';
      printf( '<a href="%s" class="%s">%s</a>', esc_url( add_query_arg( 'tab', $tab, $plugin_url ) ), $class, $title );
    }
    ?>
  </h2>

  <?php
  if ( 'dashboard' == $active_tab ) {
    $file = ! empty( $general_settings['api_key'] ) ? 'builder' : 'api-key';
    require_once AYG_DIR . "admin/templates/{$file}.php";
  }
  ?>
</div>
