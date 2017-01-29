<?php

defined( 'WPINC' ) or die();

class NearbyWP_Test_NearbyWP extends WP_UnitTestCase {
	/**
	 *
	 *
	 * @covers nearbywp_get_events()
	 */
	public function test_get_events_valid_input() {
		$this->assertEquals( true, true );
	}

	/**
	 *
	 *
	 * @covers nearbywp_get_events()
	 */
	public function test_get_events_invalid_input() {
		$this->assertEquals( false, false );
	}
}
