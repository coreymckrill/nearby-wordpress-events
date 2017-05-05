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
		array( 'wp-a11y', 'wp-util' ),
		NEARBYWP_VERSION,
		true
	);

	wp_localize_script( 'nearbywp', 'nearbyWPData', nearbywp_get_inline_script_data() );
}

/**
 * Get the data that should be passed to JavaScript
 *
 * @since 4.8.0
 *
 * @return array The script data.
 */
function nearbywp_get_inline_script_data() {
	$user_id       = get_current_user_id();
	$user_location = get_user_option( 'nearbywp-location', $user_id );
	$nearby_events = new WP_Nearby_Events( $user_id, $user_location );

	$inline_script_data = array(
		'nonce'      => wp_create_nonce( 'nearbywp_events' ),
		'cachedData' => $nearby_events->get_cached_events(),

		'l10n' => array(
			'enter_closest_city' => __( 'Enter your closest city name to find nearby events', 'nearby-wp-events' ),
			'error_occurred_please_try_again' => __( 'An error occured. Please try again.', 'nearby-wp-events' ),

			/*
			 * These specific examples were chosen to highlight the fact that a
			 * state is not needed, even for cities whose name is not unique.
			 * It would be too cumbersome to include that in the instructions
			 * to the user, so it's left as an implication.
			 */
			/* translators: %s is the name of the city we couldn't locate. Replace the examples with cities in your locale, but test that they match the expected location before including them. Use endonyms (native locale names) whenever possible. */
			'could_not_locate_city' => __( 'We couldn\'t locate <strong><em>%s</em></strong>. Please try another nearby city. For example: <em>Kansas City; Springfield; Portland<em>.', 'nearby-wp-events' ),

			// This one is only used with wp.a11y.speak(), so it can/should be more brief.
			/* translators: %s is the name of a city. */
			'city_updated' => __( 'City updated. Listing events near %s.', 'nearby-wp-events' ),
		)
	);

	return $inline_script_data;
}
