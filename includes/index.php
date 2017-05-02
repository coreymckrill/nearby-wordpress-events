<?php

defined( 'WPINC' ) || die();

/**
 * Enqueue dashboard widget scripts and styles
 *
 * @param string $hook_suffix The slug of the current screen
 *
 * @todo This function can be removed during the merge, since Core already enqueues these files.
 */
function nearbywp_enqueue_scripts( $hook_suffix ) {
	if ( 'index.php' !== $hook_suffix ) {
		return;
	}

	wp_enqueue_style( 'nearbywp' );
	wp_enqueue_script( 'nearbywp' );
}
