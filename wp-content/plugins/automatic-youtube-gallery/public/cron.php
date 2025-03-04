<?php

/**
 * Cron Jobs.
 *
 * @link    https://plugins360.com
 * @since   2.3.0
 *
 * @package Automatic_YouTube_Gallery
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AYG_Public_Cron class.
 *
 * @since 2.3.0
 */
class AYG_Public_Cron {

	/**
	 * Add a custom weekly cron schedule.
	 *
	 * @since  2.3.0
	 * @param  array $schedules An array of non-default cron schedules.
	 * @return array $schedules Filtered array of non-default cron schedules.
	 */
	public function cron_schedules( $schedules ) {
		$schedules['weekly'] = array( 
			'interval' => 7 * DAY_IN_SECONDS, 
			'display'  => __( 'Weekly', 'automatic-youtube-gallery' ) 
		);

		return $schedules;
	}

    /**
	 * Schedule an action if it's not already scheduled.
	 *
	 * @since 2.3.0
	 */
	public function schedule_events() {
		if ( ! wp_next_scheduled( 'ayg_schedule_weekly' ) ) {
			wp_schedule_event( time(), 'weekly', 'ayg_schedule_weekly' );
		}
	}

    /**
	 * Called weekly.
	 *
	 * @since 2.3.0
	 */
	public function cron_event() {
        $existing_keys = get_option( 'ayg_transient_keys', array() );

        if ( is_array( $existing_keys ) ) {
            $filtered_keys = array();

            foreach ( $existing_keys as $key ) {
                if ( get_transient( $key ) ) {
                    $filtered_keys[] = $key;
                }
            }

            update_option( 'ayg_transient_keys', $filtered_keys );
        }
	}

}
