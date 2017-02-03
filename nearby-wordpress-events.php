<?php

/*
Plugin Name: Nearby WordPress Events
Plugin URI:  http://wordpress.org/plugins/neraby-wordpress-events/
Description: Shows the current user a list of nearby WordPress events via a Dashboard widget and/or a front-end widget
Version:     0.1
Author:      WordPress
Author URI:  https://wordpress.org
Text Domain: nearbywp
License:     GPL2
*/

defined( 'WPINC' ) or die();

if ( ! is_admin() ) {
	return;
}

require_once( dirname( __FILE__ ) . '/includes/dashboard-widget.php' );

/**
 * Initialize widget functionality
 */
function nearbywp_init() {
	add_action( 'wp_dashboard_setup', 'nearbywp_register_dashboard_widgets' );
	add_action( 'admin_print_scripts-index.php', 'nearbywp_enqueue_scripts' );
}

add_action( 'load-index.php', 'nearbywp_init' );

/**
 * Register Dashboard widget
 */
function nearbywp_register_dashboard_widgets() {
	wp_add_dashboard_widget(
		'nearbywp_dashboard_events',
		esc_html__( 'Nearby WordPress Events', 'nearbywp' ),
		'nearbywp_render_dashboard_events'
	);
}

/**
 * Enqueue widget scripts and styles
 */
function nearbywp_enqueue_scripts() {
	wp_enqueue_script( 'nearbywp', plugins_url( 'js/dashboard.js', __FILE__ ), array( 'wp-util' ), 1, true );
	wp_localize_script( 'nearbywp', 'nearbyWP', array(
		'nonce' => wp_create_nonce( 'nearbywp_events' ),
		'l10n'  => array(
			'geolocate'      => __( 'Get location' ),
			'geolocateError' => __( 'Unable to retrieve current location' ),
		),
	) );

	wp_enqueue_style( 'nearbywp', plugins_url( 'css/dashboard.css', __FILE__ ), array(), 1 );
}

/**
 * Ajax handler for fetching widget events
 */
function nearbywp_get_events() {
	check_ajax_referer( 'nearbywp_events' );

	$user_id = get_current_user_id();

	// cached results
	$events = get_transient( "nearbywp-{$user_id}" );

	if ( empty( $events ) || isset( $_POST['location'] ) || true ) {
		$args = array(
			'locale'      => get_user_locale( $user_id ),
			'coordinates' => get_user_meta( $user_id, 'nearbywp', true ),
		);

		// no location
		if ( empty( $args['coordinates'] ) ) {
			if ( ! empty( $_POST['nearbywp-location'] ) ) {
				$args['location'] = wp_unslash( $_POST['location'] );
			} else {
				$args['ip']           = $_SERVER['REMOTE_ADDR'];
				$args['browser_lang'] = nearbywp_get_http_locales();
				$args['timezone']     = wp_unslash( $_POST['tz'] );
			}
		}

		$response = wp_remote_get( 'https://api.wordpress.org/events/1.0/', $args );

		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			$events = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( ! isset( $events['location'], $events['events'] ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'API Error: Invalid response.' ),
				) );
			}

			foreach ( $events['events'] as $key => $event ) {
				$events['events'][ $key ]['date'] = date_i18n( get_option( 'date_format' ), strtotime( $event['date'] ) );
			}

			set_transient( "nearbywp-{$user_id}", $events, DAY_IN_SECONDS );
			update_user_meta( $user_id, 'nearbywp', $events['coordinates'] );
		} else {
			wp_send_json_error( array(
				'message' => esc_html__( 'API Error: No response received.' ),
			) );
		}
	}

	wp_send_json_success( $events );
}

add_action( 'wp_ajax_nearbywp_get_events', 'nearbywp_get_events' );

/**
 * Given a HTTP Accept-Language header $header
 * returns all the locales in it.
 *
 * @return array Matched locales.
 */
function nearbywp_get_http_locales() {
	if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
		$locale_part_re = '[a-z]{2,}';
		$locale_re      = "($locale_part_re(\-$locale_part_re)?)";

		if ( preg_match_all( "/$locale_re/i", $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches ) ) {
			return $matches[0];
		}
	}

	return array();
}
