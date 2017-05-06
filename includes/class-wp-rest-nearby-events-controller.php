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

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'args'                => array(),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

	}

	/**
	 * Checks if a given request has access to retrieve nearby events.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool True if the request has read access for the item, otherwise false.
	 */
	public function get_item_permissions_check( $request ) {
		//return current_user_can( 'read' );
		return true;
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
		$user_location = get_user_option( 'nearby-events-location', $user_id );

		$this->events = new WP_Nearby_Events( $user_id, $user_location );

		$search   = isset( $request['location'] ) ? wp_unslash( $request['location'] ) : '';
		$timezone = isset( $request['timezone'] ) ? wp_unslash( $request['timezone'] ) : '';

		$events = $this->events->get_events( $search, $timezone );

		if ( isset( $events['location'] ) && ( $search || ! $user_location ) ) {
			// Store the location network-wide, so the user doesn't have to set it on each site.
			update_user_option( $user_id, 'nearby-events-location', $events['location'], true );
		}

		$response = $this->prepare_item_for_response( $events, $request );

		return $response;
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

		// Wrap the data in a response object.
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
