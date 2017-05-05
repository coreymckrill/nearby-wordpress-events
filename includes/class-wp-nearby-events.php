<?php
/**
 * A class to handle locations and events.
 *
 * @package WordPress
 * @subpackage Administration
 * @since 4.8.0
 */

defined( 'WPINC' ) || die();

/**
 * Class WP_Nearby_Events
 *
 * A client for api.wordpress.org/events.
 *
 * @since 4.8.0
 */
class WP_Nearby_Events {
	/**
	 * WP user ID.
	 *
	 * @access protected
	 * @since 4.8.0
	 *
	 * @var int
	 */
	protected $user_id = 0;

	/**
	 * Stored location data for the user.
	 *
	 * @access protected
	 * @since 4.8.0
	 *
	 * @var bool|array
	 */
	protected $user_location = false;

	/**
	 * WP_Nearby_Events constructor.
	 *
	 * @access public
	 * @since 4.8.0
	 *
	 * @param int        $user_id       WP user ID.
	 * @param bool|array $user_location Stored location data for the user.
	 *                                  `false` to pass no location;
	 *                                  `array` to pass a location {
	 *     @type string $description The name of the location
	 *     @type string $latitude    The latitude in decimal degrees notation, without the degree
	 *                               symbol. e.g., `47.615200`.
	 *     @type string $longitude   The longitude in decimal degrees notation, without the degree
	 *                               symbol. e.g., `-122.341100`.
	 *     @type string $country     The ISO 3166-1 alpha-2 country code. e.g., `BR`
	 * }
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
	 * The API will send back a recognized location based on the data, along with
	 * nearby events.
	 *
	 * @access public
	 * @since 4.8.0
	 *
	 * @param string $location_search Optional search string to help determine the location.
	 *                                Default empty string.
	 * @param string $timezone        Optional timezone to help determine the location.
	 *                                Default empty string.
	 * @return array|WP_Error         A WP_Error on failure; an array with location and events on
	 *                                success.
	 */
	public function get_events( $location_search = '', $timezone = '' ) {
		$cached_events = $this->get_cached_events();

		if ( ! $location_search && $cached_events ) {
			return $cached_events;
		}

		$request_url   = $this->get_request_url( $location_search, $timezone );
		$response      = wp_remote_get( $request_url );
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		$response_error = null;
		$debugging_info = compact( 'request_url', 'response_code', 'response_body' );

		if ( is_wp_error( $response ) ) {
			$response_error = $response;
		} elseif ( 200 !== $response_code ) {
			$response_error = new WP_Error(
				'api-error',
				/* translators: %s is a numeric HTTP status code; e.g., 400, 403, 500, 504, etc. */
				esc_html( sprintf( __( 'Invalid API response code (%d)', 'nearby-wp-events' ), $response_code ) )
			);
		} elseif ( ! isset( $response_body['location'], $response_body['events'] ) ) {
			$response_error = new WP_Error(
				'api-invalid-response',
				isset( $response_body['error'] ) ? $response_body['error'] : __( 'Unknown API error.', 'nearby-wp-events' )
			);
		}

		if ( is_wp_error( $response_error ) ) {
			$this->maybe_log_events_response( $response->get_error_message(), $debugging_info );

			return $response_error;
		} else {
			$this->cache_events( $response_body );

			$response_body = $this->trim_events( $response_body );
			$response_body = $this->format_event_data_time( $response_body );

			// Avoid bloating the log with all the event data, but keep the count.
			$debugging_info['response_body']['events'] = count( $debugging_info['response_body']['events'] ) . ' events trimmed.';

			$this->maybe_log_events_response( 'Valid response received', $debugging_info );

			return $response_body;
		}
	}

	/**
	 * Build a URL for requests to the Events API
	 *
	 * @access protected
	 * @since 4.8.0
	 *
	 * @param  string $search   City search string. Default empty string.
	 * @param  string $timezone Timezone string. Default empty string.
	 * @return string           The request URL.
	 */
	protected function get_request_url( $search = '', $timezone = '' ) {
		$api_url = 'https://api.wordpress.org/events/1.0/';

		$args = array(
			'number' => 5, // Get more than three in case some get trimmed out.
			'ip'     => $this->get_unsafe_client_ip(),
			'locale' => get_user_locale( $this->user_id ),
		);

		if ( $timezone ) {
			$args['timezone'] = $timezone;
		}

		if ( $search ) {
			$args['location'] = $search;
		} elseif ( isset( $this->user_location['latitude'], $this->user_location['longitude'] ) ) {
			// Send pre-determined location.
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
	 * Modified from http://stackoverflow.com/a/2031935/450127.
	 *
	 * SECURITY WARNING: This function is _NOT_ intended to be used in
	 * circumstances where the authenticity of the IP address matters. This does
	 * _NOT_ guarantee that the returned address is valid or accurate, and it can
	 * be easily spoofed.
	 *
	 * @access protected
	 * @since 4.8.0
	 *
	 * @return false|string `false` on failure, the `string` address on success
	 */
	protected function get_unsafe_client_ip() {
		$client_ip = false;

		// In order of preference, with the best ones for this purpose first.
		$address_headers = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $address_headers as $header ) {
			if ( array_key_exists( $header, $_SERVER ) ) {
				/*
				 * HTTP_X_FORWARDED_FOR can contain a chain of comma-separated
				 * addresses. The first one is the original client. It can't be
				 * trusted for authenticity, but we don't need to for this purpose.
				 */
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
	 * This could be reduced to a one-liner in the calling functions, but it's
	 * intentionally a separate function because it's called from multiple
	 * locations, and having it abstracted keeps the logic consistent and DRY,
	 * which is less prone to errors.
	 *
	 * @access protected
	 * @since 4.8.0
	 *
	 * @param  array       $location Should contain 'latitude' and 'longitude' indexes.
	 * @return bool|string           `false` on failure, or a string on success
	 */
	protected function get_events_transient_key( $location ) {
		$key = false;

		if ( isset( $location['latitude'], $location['longitude'] ) ) {
			$key = 'nearbywp-' . md5( $location['latitude'] . $location['longitude'] );
		}

		return $key;
	}

	/**
	 * Cache an array of events data from the Events API.
	 *
	 * @access protected
	 * @since 4.8.0
	 *
	 * @param array $events Response body from the API request.
	 * @return bool `true` if events were cached; `false` if not.
	 */
	protected function cache_events( $events ) {
		$set              = false;
		$transient_key    = $this->get_events_transient_key( $events['location'] );
		$cache_expiration = isset( $events['ttl'] ) ? absint( $events['ttl'] ) : HOUR_IN_SECONDS * 12;

		if ( $transient_key ) {
			$set = set_site_transient( $transient_key, $events, $cache_expiration );
		}

		return $set;
	}

	/**
	 * Get cached events
	 *
	 * @access public
	 * @since 4.8.0
	 *
	 * @return false|array `false` on failure; an array containing `location`
	 *                     and `events` items on success.
	 */
	public function get_cached_events() {
		$cached_response = get_site_transient( $this->get_events_transient_key( $this->user_location ) );
		$cached_response = $this->trim_events( $cached_response );

		return $this->format_event_data_time( $cached_response );
	}

	/**
	 * Add formatted date and time items for each event in an API response
	 *
	 * This has to be called after the data is pulled from the cache, because
	 * the cached events are shared by all users. If it was called before storing
	 * the cache, then all users would see the events in the localized data/time
	 * of the user who triggered the cache refresh, rather than their own.
	 *
	 * @access protected
	 * @since 4.8.0
	 *
	 * @param  array $response_body The response which contains the events.
	 * @return array                The response with dates and times formatted
	 */
	protected function format_event_data_time( $response_body ) {
		if ( isset( $response_body['events'] ) ) {
			foreach ( $response_body['events'] as $key => $event ) {
				$timestamp = strtotime( $event['date'] );

				/*
				 * It's important to keep the day of the week in the formatted date,
				 * so that users can tell at a glance if the event is on a day they
				 * are available, without having to open the link.
				 */
				/* translators: Date format for upcoming events on the dashboard. Include the day of the week. See https://secure.php.net/date. */
				$response_body['events'][ $key ]['formatted_date'] = date_i18n( __( 'l, M j, Y', 'nearby-wp-events' ), $timestamp );
				$response_body['events'][ $key ]['formatted_time'] = date_i18n( get_option( 'time_format' ), $timestamp );
			}
		}

		return $response_body;
	}

	/**
	 * Discard events that occurred more than 24 hours ago, then reduce the remaining list down to three items.
	 *
	 * @access protected
	 * @since 4.8.0
	 *
	 * @param  array $response_body The response body which contains the events.
	 * @return array                The response body with events trimmed.
	 */
	protected function trim_events( $response_body ) {
		if ( isset( $response_body['events'] ) ) {
			$current_timestamp = current_time('timestamp' );

			foreach ( $response_body['events'] as $key => $event ) {
				// Skip WordCamps, because they might be multi-day events.
				if ( 'meetup' !== $event['type'] ) {
					continue;
				}

				$event_timestamp = strtotime( $event['date'] );

				if ( $current_timestamp > $event_timestamp && ( $current_timestamp - $event_timestamp ) > DAY_IN_SECONDS ) {
					unset( $response_body['events'][ $key ] );
				}
			}

			$response_body['events'] = array_slice( $response_body['events'], 0, 3 );
		}

		return $response_body;
	}


	/**
	 * Log responses to Events API requests
	 *
	 * All responses are logged when debugging, even if they're not WP_Errors. See
	 * `WP_Nearby_Events::get_events()` for details.
	 *
	 * Errors are logged instead of being triggered, to avoid breaking the JSON
	 * response when called from AJAX handlers and `display_errors` is enabled.
	 *
	 * Debugging info is still needed for "successful" responses, because
	 * the API might have returned a different location than the one the user
	 * intended to receive. In those cases, knowing the exact `request_url` is
	 * critical.
	 *
	 * @access protected
	 * @since 4.8.0
	 *
	 * @param string $message        A description of what occurred
	 * @param array  $debugging_info Details that provide more context for the
	 *                               log entry
	 */
	protected function maybe_log_events_response( $message, $details ) {
		if ( ! WP_DEBUG_LOG ) {
			return;
		}

		error_log( sprintf(
			'%s: %s. Details: %s',
			__METHOD__,
			trim( $message, '.' ),
			wp_json_encode( $details )
		) );
	}
}
