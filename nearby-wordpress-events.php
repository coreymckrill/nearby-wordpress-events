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

function nearbywp_bootstrap() {
	if ( is_admin() ) {
		require_once( dirname( __FILE__ ) . '/includes/dashboard-widget.php' );
	}

	require_once( dirname( __FILE__ ) . '/includes/front-end-widget.php' );

	add_action( 'wp_dashboard_setup',    'nearbywp_register_dashboard_widgets' );
	add_action( 'widgets_init',          'nearbywp_register_front_end_widgets' );
	add_action( 'admin_enqueue_scripts', 'nearbywp_enqueue_scripts' );
	add_action( 'wp_enqueue_scripts',    'nearbywp_enqueue_scripts' );
}

function nearbywp_register_dashboard_widgets() {
	wp_add_dashboard_widget(
		'nearbywp_dashboard_events',
		esc_html__( 'Nearby WordPress Events', 'nearbywp' ),
		'nearbywp_render_dashboard_events'
	);
}

function nearbywp_register_front_end_widgets() {
	register_widget( 'NearbyWP_Front_End_Widget' );
}

function nearbywp_enqueue_scripts() {
	wp_register_script(
		'nearbywp-common-script',
		plugins_url( 'js/common.js', __FILE__ ),
		array(),
		1,
		true
	);

	wp_register_script(
		'nearbywp-dashboard-script',
		plugins_url( 'js/dashboard.js', __FILE__ ),
		array(),
		1,
		true
	);

	wp_register_script(
		'nearbywp-front-end-script',
		plugins_url( 'js/front-end.js', __FILE__ ),
		array(),
		1,
		true
	);

	wp_register_style(
		'nearbywp-dashboard-style',
		plugins_url( 'css/dashboard.css', __FILE__ ),
		array(),
		1
	);

	wp_register_style(
		'nearbywp-front-end-style',
		plugins_url( 'css/front-end.css', __FILE__ ),
		array(),
		1
	);

	wp_enqueue_script( 'nearbywp-common-script' );

	if ( is_admin() ) {
		// todo only enqueue on Dashboard screen

		wp_enqueue_script( 'nearbywp-dashboard-script' );
		wp_enqueue_style(  'nearbywp-dashboard-style'  );
	} else {
		wp_enqueue_script( 'nearbywp-front-end-script' );
		wp_enqueue_style(  'nearbywp-front-end-style'  );
	}
}

function nearbywp_get_events( $location ) {
	$events = array();  // remote_get api.wordpress.org/core/widgets/nearby-events/1.0/?location=$location

	return $events;
}

nearbywp_bootstrap();
