<?php
/**
 * @package Nearby WordPress Events
 */

defined( 'WPINC' ) or die();

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
		$cached_events = $this->get_cached_events();

		if ( ! $location_search && $cached_events ) {
			return $cached_events;
		}

		$request_url   = $this->build_api_request_url( $location_search, $timezone );

		$response      = wp_remote_get( $request_url );
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $response_code ) {
			return new WP_Error(
				'api-error',
				esc_html( sprintf( __( 'API Error: %s' ), $response_code ) ),
				compact( 'request_url', 'response_code', 'events' ) // @todo remove this during merge to Core
			);
		}

		if ( ! isset( $response_body['location'], $response_body['events'] ) ) {
			return new WP_Error(
				'api-invalid-response',
				isset( $response_body['error'] ) ? $response_body['error'] : __( 'API Error: Invalid response.' ),
				compact( 'request_url', 'response_code', 'events' ) // @todo remove this during merge to Core
			);
		}

		foreach ( $response_body['events'] as $key => $event ) {
			/* translators: date and time format for upcoming events on the dashboard, see https://secure.php.net/date */
			$response_body['events'][ $key ]['date'] = date_i18n( __( 'M j, Y' ), strtotime( $event['date'] ) );
		}

		$this->cache_events( $response_body );

		return $response_body;
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

	/**
	 * Get cached events
	 *
	 * @return false|array `false` on failure; an array containing `location`
	 *                     and `events` items on success
	 */
	public function get_cached_events() {
		return get_transient( $this->get_events_transient_key() );
	}
}