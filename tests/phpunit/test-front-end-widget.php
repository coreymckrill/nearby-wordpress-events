<?php

defined( 'WPINC' ) or die();

/**
 *
 *
 * @covers NearbyWP_Front_End_Widget
 */
class NearbyWP_Test_Front_End_Widget extends WP_UnitTestCase {
	/**
	 *
	 *
	 * @covers get_visitor_location()
	 */
	public function test_visitor_location_valid_input() {
		$this->assertEquals( true, true );
	}

	/**
	 *
	 *
	 * @covers get_visitor_location()
	 */
	public function test_visitor_location_invalid_input() {
		$this->assertEquals( false, false );
	}
}
