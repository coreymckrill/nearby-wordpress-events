<?php
/**
 * REST API: WP_REST_Nearby_Events_Controller class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.8.0
 */

/**
 * Core class used to retrieve upcoming WordPress events via the REST API.
 *
 * @since 4.8.0
 *
 * @see WP_REST_Controller
 */
class WP_REST_Nearby_Events_Controller extends WP_REST_Controller {

	/**
	 * Instance of a nearby events object.
	 *
	 * @since 4.8.0
	 * @access protected
	 * @var WP_Nearby_Events
	 */
	protected $events;

	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function __construct() {
		$this->namespace = 'wp/v2';
		$this->rest_base = 'nearby_events';
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/me', array(
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_item' ),
				'args'     => $this->get_item_args(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Retrieves nearby events.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error WP_REST_Response on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$user_id       = get_current_user_id();
		if ( empty( $user_id ) ) {
			return new WP_Error( 'rest_not_logged_in', __( 'You are not currently logged in.' ), array( 'status' => 401 ) );
		}

		$user_location = get_user_option( 'nearbywp-location', $user_id );

		$this->events = new WP_Nearby_Events( $user_id, $user_location );
		$events = $this->events->get_events( $request['location'], $request['timezone'] );

		if ( ! $user_location && ! is_wp_error( $events ) && isset( $events['location'] ) ) {
			// Store the location network-wide, so the user doesn't have to set it on each site.
			update_user_option( $user_id, 'nearbywp-location', $events['location'], true );
		}

		$response = $this->prepare_item_for_response( $events, $request );

		return $response;
	}

	/**
	 * Retrieves the schema for valid arguments in a nearby events request.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @return array Argument schema data.
	 */
	public function get_item_args() {
		return array(
			'location' => array(
				'description'       => __( '', 'nearby-wp-events' ),
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'timezone' => array(
				'description'       => __( '', 'nearby-wp-events' ),
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);
	}

	/**
	 * Prepares a nearby events array for response.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param array|WP_Error  $item    Array containing nearby events data.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$response = array();

		if ( is_wp_error( $item ) ) {
			$response['error'] = $item->get_error_message();
		} else {
			$response = $item;
		}

		$response = rest_ensure_response( $response );

		return $response;
	}

	/**
	 * Retrieves the schema for a nearby events response, conforming to JSON Schema.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'nearby_events',
			'type'       => 'object',
			'properties' => array(
				'location' => array(
					'description' => '',
					'type' => 'object',
					'properties' => array(
						'description' => array(
							'description' => '',
							'type'        => 'string',
						),
						'latitude'    => array(
							'description' => '',
							'type'        => 'float',
						),
						'longitude'   => array(
							'description' => '',
							'type'        => 'float',
						),
						'country'     => array(
							'description' => '',
							'type'        => 'string',
						),
					),
				),
				'events' => array(
					'description' => '',
					'type' => 'array',
					'items' => array(
						'type' => 'object',
						'properties' => array(
							'type' => array(
								'description' => '',
								'type' => 'string',
							),
							'title' => array(
								'description' => '',
								'type' => 'string',
							),
							'url' => array(
								'description' => '',
								'type' => 'string',
							),
							'meetup' => array(
								'description' => '',
								'type' => 'string',
							),
							'meetup_url' => array(
								'description' => '',
								'type' => 'string',
							),
							'date' => array(
								'description' => '',
								'type' => 'string',
							),
							'formatted_date' => array(
								'description' => '',
								'type' => 'string',
							),
							'formatted_time' => array(
								'description' => '',
								'type' => 'string',
							),
							'location' => array(
								'description' => '',
								'type' => 'object',
								'properties' => array(
									'location' => array(
										'description' => '',
										'type' => 'string',
									),
									'country' => array(
										'description' => '',
										'type' => 'string',
									),
									'latitude' => array(
										'description' => '',
										'type' => 'float',
									),
									'longitude' => array(
										'description' => '',
										'type' => 'float',
									),
								),
							),
						),
					),
				),
				'error' => array(
					'description' => '',
					'type' => 'string',
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}
}
