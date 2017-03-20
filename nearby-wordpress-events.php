<?php

/*
Plugin Name: Nearby WordPress Events
Plugin URI:  http://wordpress.org/plugins/neraby-wordpress-events/
Description: Shows the current user a list of nearby WordPress events via a Dashboard widget.
Version:     0.1
Author:      WordPress
Author URI:  https://wordpress.org
Text Domain: nearby-wordpress-events
License:     GPL2
*/

defined( 'WPINC' ) or die();

define( 'NEARBYWP_VERSION', '0.1' );

if ( ! is_admin() ) {
	return;
}

require_once( dirname( __FILE__ ) . '/includes/class-wp-nearby-events.php' );
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
		esc_html__( 'WordPress Events and News', 'nearby-wordpress-events' ),
		'nearbywp_render_dashboard_widget'
	);

	// Remove WordPress News because we'll incorporate its contents into the new widget
	remove_meta_box( 'dashboard_primary', get_current_screen(), 'side' );
}

/**
 * Enqueue dashboard widget scripts and styles
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
function nearbywp_ajax_get_events() {
	check_ajax_referer( 'nearbywp_events' );

	$search   = isset( $_POST['location'] ) ? $_POST['location'] : '';
	$timezone = isset( $_POST['timezone'] ) ? $_POST['timezone'] : '';

	$user_id       = get_current_user_id();
	$user_location = get_user_meta( $user_id, 'nearbywp-location', true );

	$nearby_events = new WP_Nearby_Events( $user_id, $user_location );
	$events        = $nearby_events->get_events( $search, $timezone );

	if ( is_wp_error( $events ) ) {
		wp_send_json_error( array(
			'message' => $events->get_error_message(),

			// @todo remove this during merge to Core
			'api_request_info' => $events->get_error_data(),
		) );
	}

	if ( isset( $events['location'] ) && ( $search || ! $user_location ) ) {
		update_user_meta( $user_id, 'nearbywp-location', $events['location'] );
	}

	// @todo remove this during merge to Core
	$events['api_request_info'] = compact( 'request_url', 'response_code' );

	wp_send_json_success( $events );
}

add_action( 'wp_ajax_nearbywp_get_events', 'nearbywp_ajax_get_events' );