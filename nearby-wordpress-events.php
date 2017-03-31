<?php

/*
Plugin Name: Nearby WordPress Events
Plugin URI:  https://wordpress.org/plugins/nearby-wp-events/
Description: Shows the current user a list of nearby WordPress events via a Dashboard widget.
Version:     0.2
Author:      WordPress Meta Team
Author URI:  https://make.wordpress.org/meta
Text Domain: nearby-wp-events
License:     GPL2
*/

defined( 'WPINC' ) or die();

define( 'NEARBYWP_VERSION', '0.2' );

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
		esc_html__( 'WordPress Events and News', 'nearby-wp-events' ),
		'nearbywp_render_dashboard_widget'
	);

	// Remove WordPress News because we'll incorporate its contents into the new widget
	remove_meta_box( 'dashboard_primary', get_current_screen(), 'side' );
}

/**
 * Enqueue dashboard widget scripts and styles
 */
function nearbywp_enqueue_scripts() {
	$user_id       = get_current_user_id();
	$user_location = get_user_meta( $user_id, 'nearbywp-location', true );
	$nearby_events = new WP_Nearby_Events( $user_id, $user_location );

	$inline_script_data = array(
		'nonce'      => wp_create_nonce( 'nearbywp_events' ),
		'cachedData' => $nearby_events->get_cached_events(),
	);

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

	wp_add_inline_script(
		'nearbywp',
		sprintf( 'var nearbyWP = %s;', wp_json_encode( $inline_script_data ), 'before' )
	);
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
			'api_request_info' => $events->get_error_data(), // @todo remove this during merge to Core
		) );
	}

	if ( isset( $events['location'] ) && ( $search || ! $user_location ) ) {
		update_user_meta( $user_id, 'nearbywp-location', $events['location'] );
	}

	wp_send_json_success( $events );
}

add_action( 'wp_ajax_nearbywp_get_events', 'nearbywp_ajax_get_events' );
