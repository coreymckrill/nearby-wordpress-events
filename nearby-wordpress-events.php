<?php

/*
Plugin Name: Nearby WordPress Events
Plugin URI:  http://wordpress.org/plugins/neraby-wordpress-events/
Description: Shows the current user a list of nearby WordPress events via a Dashboard widget.
Version:     0.1
Author:      WordPress
Author URI:  https://wordpress.org
Text Domain: nearbywp
License:     GPL2
*/

defined( 'WPINC' ) or die();

define( 'NEARBYWP_VERSION', '0.1' );

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
		esc_html__( 'WordPress Events and News', 'nearbywp' ),
		'nearbywp_render_dashboard_widget'
	);

	// Remove WordPress News
	remove_meta_box( 'dashboard_primary', get_current_screen(), 'side' );
}

/**
 * Enqueue widget scripts and styles
 */
function nearbywp_enqueue_scripts() {
	wp_enqueue_style(
		'nearbywp',
		plugins_url( 'css/dashboard.css', __FILE__ ),
		array(),
		NEARBYWP_VERSION
	);

	wp_enqueue_script(
		'nearbywp',
		plugins_url( 'js/dashboard.js', __FILE__ ),
		array( 'wp-util' ),
		NEARBYWP_VERSION,
		true
	);

	wp_localize_script( 'nearbywp', 'nearbyWP', array(
		'nonce' => wp_create_nonce( 'nearbywp_events' ),
	) );
}

/**
 * Ajax handler for fetching widget events
 */
function nearbywp_get_events() {
	check_ajax_referer( 'nearbywp_events' );

	$api_url = 'https://api.wordpress.org/events/1.0/';

	$user_id = get_current_user_id();
	$user_location = get_user_meta( $user_id, 'nearbywp-location', true );
	$transient_key = 'nearbywp-' . md5( maybe_serialize( $user_location ) );

	// cached results
	$events = get_transient( $transient_key );

	if ( empty( $events ) || isset( $_POST['location'] ) ) {
		$args = array(
			'number' => 3,
			'ip'     => nearbywp_get_unsafe_client_ip(),
			'locale' => ( function_exists( 'get_user_locale' ) ) ? get_user_locale( $user_id ) : get_locale(),
		);

		if ( isset( $_POST['tz'] ) ) {
			$args['timezone'] = wp_unslash( $_POST['tz'] );
		}

		if ( isset( $_POST['location'] ) ) {
			$args['location'] = wp_unslash( $_POST['location'] );
		} else if ( isset( $user_location['latitude'], $user_location['longitude'] ) ) {
			// Send pre-determined location
			$args['latitude']  = $user_location['latitude'];
			$args['longitude'] = $user_location['longitude'];
		}

		$request_url = add_query_arg( $args, $api_url );
		$response = wp_remote_get( $request_url );
		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $response_code ) {
			$events = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( ! isset( $events['location'], $events['events'] ) ) {
				$message = ( isset( $events['error'] ) ) ? $events['error'] : __( 'API Error: Invalid response.' );

				wp_send_json_error( array(
					'message' => esc_html( $message ),
					'api_request_info' => compact( 'request_url', 'response_code', 'events' ),    // @todo remove this during merge to Core
				) );
			}

			foreach ( $events['events'] as $key => $event ) {
				/* translators: date and time format for upcoming events on the dashboard, see https://secure.php.net/date */
				$events['events'][ $key ]['date'] = date_i18n( __( 'M j, Y' ), strtotime( $event['date'] ) );
			}

			$cache_expiration = ( isset( $events['ttl'] ) ) ? absint( $events['ttl'] ) : HOUR_IN_SECONDS * 12;

			set_transient( $transient_key, $events, $cache_expiration );

			if ( isset( $_POST['location'] ) || ! $user_location ) {
				update_user_meta( $user_id, 'nearbywp-location', $events['location'] );
			}
		} else {
			wp_send_json_error( array(
				'message' => esc_html( sprintf(
					__( 'API Error: %s' ),
					$response_code
				) ),
				'api_request_info' => compact( 'request_url', 'response_code', 'events' ),    // @todo remove this during merge to Core
			) );
		}
	}

	$events['api_request_info'] = compact( 'request_url', 'response_code' );    // @todo remove this during merge to Core
	wp_send_json_success( $events );
}
add_action( 'wp_ajax_nearbywp_get_events', 'nearbywp_get_events' );

/**
 * Determine the user's actual IP if possible
 *
 * If the user is making their request through a proxy, or if the web server
 * is behind a proxy, then $_SERVER['REMOTE_ADDR'] will be the proxy address
 * rather than the user.
 *
 * Modified from http://stackoverflow.com/a/2031935/450127
 *
 * SECURITY WARNING: This function is _NOT_ intended to be used in
 * circumstances where the authenticity of the IP address matters. This does
 * _NOT_ guarantee that the returned address is valid or accurate, and it can
 * be easily spoofed.
 *
 * @return false|string `false` on failure, the `string` address on success
 */
function nearbywp_get_unsafe_client_ip() {
	$client_ip = false;

	// In order of preference, with the best ones for this purpose first
	$address_headers = array(
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR'
	);

	foreach ( $address_headers as $header ) {
		if ( array_key_exists( $header, $_SERVER ) ) {
			// HTTP_X_FORWARDED_FOR can contain a chain of comma-separated
			// addresses. The first one is the original client. It can't be
			// trusted for authenticity, but we don't need to for this purpose.
			$address_chain = explode( ',', $_SERVER[ $header ] );
			$client_ip     = trim( $address_chain[0] );

			break;
		}
	}

	return $client_ip;
}
