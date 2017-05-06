<?php

defined( 'WPINC' ) || die();

/**
 * Register REST route.
 *
 * @todo This function can be removed during the merge, since it will be incorporated into
 *       Core's create_initial_rest_routes() function.
 */
function nearbywp_create_initial_rest_routes() {
	$controller = new WP_REST_Nearby_Events_Controller;
	$controller->register_routes();
}

add_action( 'rest_api_init', 'nearbywp_create_initial_rest_routes', 99 );
