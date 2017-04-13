<?php

class Test_WP_Nearby_Events extends WP_UnitTestCase {

	private $instance;

	public function setUp() {
		parent::setUp();

		$this->instance = new WP_Nearby_Events( 1, $this->get_user_location() );
	}

	private function get_user_location() {
		return array(
			'description' => 'San Francisco',
			'latitude'    => '37.7749300',
			'longitude'   => '-122.4194200',
			'country'     => 'US',
		);
	}

	public function test_get_events_bad_response_code() {
		add_filter( 'pre_http_request', array( $this, 'http_request_bad_response_code' ) );

		$this->assertWPError( $this->instance->get_events() );

		remove_filter( 'pre_http_request', array( $this, 'http_request_bad_response_code' ) );
	}

	public function http_request_bad_response_code() {
		return array(
			'headers'  => '',
			'body'     => '',
			'response' => array(
				'code' => 404
			),
			'cookies'  => '',
			'filename' => '',
		);
	}

	public function test_get_events_invalid_response() {
		add_filter( 'pre_http_request', array( $this, 'http_request_invalid_response' ) );

		$this->assertWPError( $this->instance->get_events() );

		remove_filter( 'pre_http_request', array( $this, 'http_request_invalid_response' ) );
	}

	public function http_request_invalid_response() {
		return array(
			'headers'  => '',
			'body'     => wp_json_encode( array() ),
			'response' => array(
				'code' => 200
			),
			'cookies'  => '',
			'filename' => '',
		);
	}

	public function test_get_events_valid_response() {
		add_filter( 'pre_http_request', array( $this, 'http_request_valid_response' ) );

		$response = $this->instance->get_events();

		$this->assertNotWPError( $response );
		$this->assertEqualSetsWithIndex( $this->get_user_location(), $response['location'] );
		$this->assertEquals( 'Sunday, Apr 16, 2017', $response['events'][0]['formatted_date'] );
		$this->assertEquals( '1:00 pm', $response['events'][0]['formatted_time'] );

		remove_filter( 'pre_http_request', array( $this, 'http_request_valid_response' ) );
	}

	public function http_request_valid_response() {
		return array(
			'headers'  => '',
			'body'     => wp_json_encode( array(
				'location' => $this->get_user_location(),
				'events'   => array(
					array(
						'type'           => 'meetup',
						'title'          => 'Flexbox + CSS Grid: Magic for Responsive Layouts',
						'url'            => 'https://www.meetup.com/Eastbay-WordPress-Meetup/events/236031233/',
						'meetup'         => 'The East Bay WordPress Meetup Group',
						'meetup_url'     => 'https://www.meetup.com/Eastbay-WordPress-Meetup/',
						'date'           => '2017-04-16 13:00:00',
						'location'       => array(
							'location'  => 'Oakland, CA, USA',
							'country'   => 'us',
							'latitude'  => 37.808453,
							'longitude' => -122.26593,
						),
					),
					array(
						'type'           => 'meetup',
						'title'          => 'Part 3- Site Maintenance - Tools to Make It Easy',
						'url'            => 'https://www.meetup.com/Wordpress-Bay-Area-CA-Foothills/events/237706839/',
						'meetup'         => 'WordPress Bay Area Foothills Group',
						'meetup_url'     => 'https://www.meetup.com/Wordpress-Bay-Area-CA-Foothills/',
						'date'           => '2017-04-26 13:30:00',
						'location'       => array(
							'location'  => 'Milpitas, CA, USA',
							'country'   => 'us',
							'latitude'  => 37.432813,
							'longitude' => -121.907095,
						),
					),
					array(
						'type'           => 'wordcamp',
						'title'          => 'WordCamp Kansas City',
						'url'            => 'https://2017.kansascity.wordcamp.org',
						'meetup'         => null,
						'meetup_url'     => null,
						'date'           => '2017-04-28 00:00:00',
						'location'       => array(
							'location'  => 'Kansas City, MO',
							'country'   => 'US',
							'latitude'  => 39.0392325,
							'longitude' => -94.577076,
						),
					),
				),
			) ),
			'response' => array(
				'code' => 200
			),
			'cookies'  => '',
			'filename' => '',
		);
	}


	public function test_build_api_request_url() {
		// @todo
	}


	public function test_get_events_transient_key() {
		// @todo
	}


	public function test_cache_events() {
		// @todo
	}


	public function test_get_cached_events() {
		// @todo
	}


	public function test_format_event_data_time() {
		// @todo
	}

	/**
	 * Call protected/private method of a class.
	 *
	 * @link https://jtreminio.com/2013/03/unit-testing-tutorial-part-3-testing-protected-private-methods-coverage-reports-and-crap/
	 *
	 * @param object &$object    Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array  $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	private function invokeMethod( &$object, $methodName, array $parameters = array() ) {
		$reflection = new ReflectionClass( get_class( $object ) );
		$method = $reflection->getMethod( $methodName );
		$method->setAccessible( true );

		return $method->invokeArgs( $object, $parameters );
	}
}