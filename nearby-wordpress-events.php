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

/**
 * Class WP_Nearby_Events
 */
class WP_Nearby_Events {
	/**
	 * @var int
	 */
	private $user_id = 0;

	/**
	 * @var bool|array
	 */
	private $user_location = false;

	/**
	 * WP_Nearby_Events constructor.
	 *
	 * @param int        $user_id
	 * @param bool|array $user_location
	 */
	public function __construct( $user_id, $user_location = false ) {
		$this->user_id       = absint( $user_id );
		$this->user_location = $user_location;
	}

	/**
	 * Get data about events near a particular location.
	 *
	 * If the `user_location` property is set and there are cached events for this
	 * location, these will be immediately returned.
	 *
	 * If not, this method will send a request to the Events API with location data.
	 * The API will send back a recongized location based on the data, along with
	 * nearby events.
	 *
	 * @param string $location_search Optional search string to help determine the location.
	 * @param string $timezone        Optional timezone to help determine the location.
	 *
	 * @return array|WP_Error
	 */
	public function get_events( $location_search = '', $timezone = '' ) {
		$cached_events = get_transient( $this->get_events_transient_key() );

		if ( ! $location_search && $cached_events ) {
			return $cached_events;
		}

		$request_url   = $this->build_api_request_url( $location_search, $timezone );

		$response      = wp_remote_get( $request_url );
		$response_code = wp_remote_retrieve_response_code( $response );
		$events        = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $response_code ) {
			return new WP_Error(
				'api-error',
				esc_html( sprintf( __( 'API Error: %s' ), $response_code ) ),
				compact( 'request_url', 'response_code', 'events' ) // @todo remove this during merge to Core
			);
		}

		if ( ! isset( $events['location'], $events['events'] ) ) {
			return new WP_Error(
				'api-invalid-response',
				isset( $events['error'] ) ? $events['error'] : __( 'API Error: Invalid response.' ),
				compact( 'request_url', 'response_code', 'events' ) // @todo remove this during merge to Core
			);
		}

		foreach ( $events['events'] as $key => $event ) {
			/* translators: date and time format for upcoming events on the dashboard, see https://secure.php.net/date */
			$events['events'][ $key ]['date'] = date_i18n( __( 'M j, Y' ), strtotime( $event['date'] ) );
		}

		$this->cache_events( $events );

		return $events;
	}

	/**
	 * Build a URL for requests to the Events API
	 *
	 * @param string $search
	 * @param string $timezone
	 *
	 * @return string
	 */
	private function build_api_request_url( $search = '', $timezone = '' ) {
		$api_url = 'https://api.wordpress.org/events/1.0/';

		$args = array(
			'number' => 3,
			'ip'     => $this->get_unsafe_client_ip(),
			'locale' => get_user_locale( $this->user_id )
		);

		if ( $timezone ) {
			$args['timezone'] = wp_unslash( $timezone );
		}

		if ( $search ) {
			$args['location'] = wp_unslash( $search );
		} else if ( isset( $this->user_location['latitude'], $this->user_location['longitude'] ) ) {
			// Send pre-determined location
			$args['latitude']  = $this->user_location['latitude'];
			$args['longitude'] = $this->user_location['longitude'];
		}

		return add_query_arg( $args, $api_url );
	}

	/**
	 * Determine the user's actual IP if possible
	 *
	 * If the user is making their request through a proxy, or if the web server
	 * is behind a proxy, then $_SERVER['REMOTE_ADDR'] will be the proxy address
	 * rather than the user's actual address.
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
	private function get_unsafe_client_ip() {
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

	/**
	 * Generate a transient key based on user location
	 *
	 * This is only one line, but it's a separate function because it's used multiple
	 * times, and having it abstracted keeps the logic consistent and DRY, which is
	 * less prone to errors.
	 *
	 * @param bool|array $location
	 *
	 * @return string
	 */
	private function get_events_transient_key( $location = false ) {
		$data = ( false === $location ) ? $this->user_location : $location;

		return 'nearbywp-' . md5( wp_json_encode( $data ) );
	}

	/**
	 * Cache an array of events data from the Events API.
	 *
	 * @param array $events
	 *
	 * @return bool
	 */
	private function cache_events( $events ) {
		$transient_key    = $this->get_events_transient_key( $events['location'] );
		$cache_expiration = isset( $events['ttl'] ) ? absint( $events['ttl'] ) : HOUR_IN_SECONDS * 12;

		return set_transient( $transient_key, $events, $cache_expiration );
	}
}