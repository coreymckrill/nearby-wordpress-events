<?php

if ( 'cli' !== php_sapi_name() ) {
	return;
}

$core_tests_directory = getenv( 'WP_TESTS_DIR' );

if ( ! $core_tests_directory ) {
	echo "\nPlease set the WP_TESTS_DIR environment variable to the folder where WordPress' PHPUnit tests live --";
	echo "\ne.g., export WP_TESTS_DIR=/srv/www/wordpress-develop/tests/phpunit\n";

	return;
}

require_once( $core_tests_directory . '/includes/functions.php' );
require_once( dirname( dirname( $core_tests_directory ) ) . '/src/wp-admin/includes/plugin.php' );

/**
 * Load the plugins that we'll need to be active for the tests
 */
function manually_load_plugin() {
	$_SERVER['PHP_SELF'] = admin_url();

	// Defining WP_ADMIN is so that nearby-wp-events/bootstrap.php will load the dashboard-widgets.php files
 	define( 'WP_ADMIN', true );

	require_once( dirname( dirname( dirname( __FILE__ ) ) ) . '/nearby-wordpress-events.php' );
}
tests_add_filter( 'muplugins_loaded', __NAMESPACE__ . '\manually_load_plugin' );

require( $core_tests_directory . '/includes/bootstrap.php' );
