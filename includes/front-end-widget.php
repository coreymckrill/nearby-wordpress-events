<?php

defined( 'WPINC' ) or die();

class NearbyWP_Front_End_Widget extends WP_Widget {
	public $instance, $defaults;

	function __construct() {
		$this->defaults = array();
		$widget_options = array(
			'description' => __( 'Shows the current user a list of nearby WordPress events', 'nearbywp' )
		);

		parent::__construct(
			'nearbywp_dashboard_events',
			esc_html__( 'Nearby WordPress Events', 'nearbywp' ),
			$widget_options
		);
	}

	public function widget( $args, $instance ) {
		$this->instance = wp_parse_args( $instance, $this->defaults );
		$events         = nearbywp_get_events( $this->get_visitor_location() );

		echo $args['before_widget'];
		echo $args['after_widget'];

		?>

		<ul class="ul-disc">
			<li><a href="">Foo</a></li>
			<li><a href="">Bar</a></li>
		</ul>

		<?php
	}

	protected function get_visitor_location() {
		return 'Barcelona';
	}

	public function form( $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults );

		?>

		<p>Any widget options go here</p>

		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();

		if ( in_array( $new_instance['foo'], array( 'bar', 'lorum', 'ipsum' ) ) ) {
			$instance['foo'] = $new_instance['foo'];
		}

		if ( isset( $new_instance['bar'] ) ) {
			$instance['bar'] = intval( $new_instance['bar'] );
		}

		return $instance;
	}
}
