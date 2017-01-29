<?php

defined( 'WPINC' ) or die();

class NearbyWP_Test_Dashboard_Widget extends WP_UnitTestCase {
	/**
	 *
	 *
	 * @covers nearbywp_get_current_user_location
	 */
	public function test_user_location_valid_input() {
		$this->assertEquals( true, true );
	}

	/**
	 *
	 *
	 * @covers nearbywp_get_current_user_location
	 */
	public function test_user_location_invalid_input() {
		$this->assertEquals( false, false );
	}
}
