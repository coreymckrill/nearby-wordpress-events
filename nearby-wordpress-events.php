<?php
/**
 * Plugin Name: Nearby WordPress Events
 * Plugin URI:  https://wordpress.org/plugins/nearby-wp-events/
 * Description: Shows the current user a list of nearby WordPress events via a Dashboard widget.
 * Version:     0.8
 * Author:      WordPress Meta Team
 * Author URI:  https://make.wordpress.org/meta
 * Text Domain: nearby-wp-events
 * License:     GPL2
 *
 * @package Nearby WordPress Events
 */

defined( 'WPINC' ) || die();

nearbywp_bootstrap();

/**
 * Bootstrap the plugin
 */
function nearbywp_bootstrap() {
	$is_dashboard_request  = '/wp-admin/index.php' === substr( $_SERVER['SCRIPT_FILENAME'], -19 ) ||
	                         '/wp-admin/network/index.php' === substr( $_SERVER['SCRIPT_FILENAME'], -27 );
	$is_event_ajax_request = wp_doing_ajax() && isset( $_REQUEST['action'] ) && 'nearbywp_get_events' === $_REQUEST['action'];

	if ( ! $is_dashboard_request && ! $is_event_ajax_request ) {
		return;
	}

	if ( nearbywp_merge_detected() ) {
		return;
	}

	define( 'NEARBYWP_VERSION', '0.8' );

	require_once( dirname( __FILE__ ) . '/includes/ajax-actions.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wp-nearby-events.php' );
	require_once( dirname( __FILE__ ) . '/includes/dashboard.php' );
	require_once( dirname( __FILE__ ) . '/includes/index.php' );
	require_once( dirname( __FILE__ ) . '/includes/script-loader.php' );

	add_action( 'load-index.php', 'nearbywp_init' );
	add_action( 'wp_ajax_nearbywp_get_events', 'nearbywp_ajax_get_events' );
}

/**
 * Initialize widget functionality
 */
function nearbywp_init() {
	add_action( 'wp_dashboard_setup',                   'nearbywp_register_dashboard_widgets' );
	add_action( 'wp_network_dashboard_setup',           'nearbywp_register_dashboard_widgets' );
	add_action( 'admin_enqueue_scripts',                'nearbywp_register_scripts', 9 );  // before nearbywp_enqueue_scripts() gets called.
	add_action( 'admin_enqueue_scripts',                'nearbywp_enqueue_scripts' );
	add_action( 'admin_print_footer_scripts-index.php', 'nearbywp_render_js_templates' );
}

/**
 * Detect whether or not this plugin has been merged into Core.
 *
 * @todo During the merge to Core, `nearbywp_ajax_get_events()` or
 *       `WP_Nearby_Events::get_events()`  must be renamed to
 *       `wp_get_nearby_events` or `wp_ajax_get_nearby_events` to preserve
 *       back-compat with this function. Otherwise, sites with the plugin
 *       installed could break. If neither of those names is desired then,
 *       hopefully we can just put a stub for one of them in
 *       `wp-admin/includes/deprecated.php`.
 *
 * @todo Remove this during the merge to Core
 *
 * @return bool
 */
function nearbywp_merge_detected() {
	/*
	 * `async-upload.php` includes `ajax-actions.php` in an unsafe manner --
	 * with `include()` instead of `include_once()` -- so we have to be careful
	 * not to cause fatal errors because of re-defined functions.
	 */
	if ( 'async-upload.php' !== $_SERVER['SCRIPT_FILENAME'] ) {
		require_once( ABSPATH . '/wp-admin/includes/ajax-actions.php' );
	}

	require_once( ABSPATH . '/wp-admin/includes/dashboard.php'  );
	require_once( ABSPATH . '/wp-admin/includes/deprecated.php' );

	$funcs = array(
		'wp_get_community_events_script_data',
		'wp_dashboard_events_news',
		'wp_print_community_events_markup',
		'wp_ajax_get_community_events',
		'rest_get_community_events',
	);

	$merged = false;

	foreach ( $funcs as $func ) {
		if ( function_exists( $func ) ) {
			$merged = true;
			break;
		}
	}

	return $merged;
}
