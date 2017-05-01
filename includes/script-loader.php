<?php

defined( 'WPINC' ) || die();

/**
 * Register dashboard widget scripts and styles
 *
 * @todo The register calls be removed during the merge, since Core already registers these files. We'll still need to add the inline script, though.
 */
function nearbywp_register_scripts() {
	wp_register_style(
		'nearbywp',
		plugins_url( 'css/dashboard.css', dirname( __FILE__ ) ),
		array(),
		NEARBYWP_VERSION
	);

	wp_register_script(
		'nearbywp',
		plugins_url( 'js/dashboard.js', dirname( __FILE__ ) ),
		array( 'wp-util' ),
		NEARBYWP_VERSION,
		true
	);

	wp_add_inline_script(
		'nearbywp',
		sprintf( 'var nearbyWPData = %s;', wp_json_encode( nearbywp_get_inline_script_data() ), 'before' )
	);
}

/**
 * Get the data that should be passed to JavaScript
 *
 * @return array
 */
function nearbywp_get_inline_script_data() {
	$user_id       = get_current_user_id();
	$user_location = get_user_option( 'nearbywp-location', $user_id );
	$nearby_events = new WP_Nearby_Events( $user_id, $user_location );

	$inline_script_data = array(
		'nonce'      => wp_create_nonce( 'nearbywp_events' ),
		'cachedData' => $nearby_events->get_cached_events(),

		'i18n' => array(
			/* translators: %s is the detailed error message. */
			'errorOccurredPleaseTryAgain' => __( 'An error occured while trying to retrieve events. Please try again.', 'nearby-wp-events' ),

			/*
			 * These specific examples were chosen to highlight the fact that a
			 * state is not needed, even for cities whose name is not unique.
			 * It would be too cumbersome to include that in the instructions
			 * to the user, so it's left as an implication.
			 */
			/* translators: %s is the name of the city we couldn't locate. Replace the examples with cities in your locale, but test that they match the expected location before including them. Use endonyms (native locale names) whenever possible. */
			'couldNotLocateCity' => __( 'We couldn\'t locate <strong><em>%1$s</em></strong>. Please try another nearby city. For example: <em>Kansas City; Springfield; Portland<em>.', 'nearby-wp-events' ),
		)
	);

	return $inline_script_data;
}
