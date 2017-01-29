<?php

defined( 'WPINC' ) or die();

function nearbywp_render_dashboard_events( $events ) {
	$events = nearbywp_get_events( nearbywp_get_current_user_location() );

	?>

	<ul class="ul-disc">
		<li><a href="">Foo</a></li>
		<li><a href="">Bar</a></li>
	</ul>

	<?php
}

function nearbywp_get_current_user_location() {
	return 'Barcelona';
}
