<?php

defined( 'WPINC' ) || die();

/**
 * Ajax handler for fetching widget events
 *
 * @since 4.8.0
 */
function nearbywp_ajax_get_events() {
	check_ajax_referer( 'nearbywp_events' );

	$search   = isset( $_POST['location'] ) ? wp_unslash( $_POST['location'] ) : '';
	$timezone = isset( $_POST['timezone'] ) ? wp_unslash( $_POST['timezone'] ) : '';

	$user_id       = get_current_user_id();
	$user_location = get_user_option( 'nearbywp-location', $user_id );

	$nearby_events = new WP_Nearby_Events( $user_id, $user_location );
	$events        = $nearby_events->get_events( $search, $timezone );

	if ( is_wp_error( $events ) ) {
		wp_send_json_error( array(
			'error' => $events->get_error_message(),
		) );
	} else {
		if ( isset( $events['location'] ) && ( $search || ! $user_location ) ) {
			// Store the location network-wide, so the user doesn't have to set it on each site.
			update_user_option( $user_id, 'nearbywp-location', $events['location'], true );
		}

		wp_send_json_success( $events );
	}
}
