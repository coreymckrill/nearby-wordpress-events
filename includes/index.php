<?php

defined( 'WPINC' ) || die();

/**
 * Enqueue dashboard widget scripts and styles
 *
 * @todo This function can be removed during the merge, since Core already enqueues these files.
 */
function nearbywp_enqueue_scripts() {
	wp_enqueue_style( 'nearbywp' );
	wp_enqueue_script( 'nearbywp' );
}
