<?php

defined( 'WPINC' ) or die();

function nearbywp_render_dashboard_events() {
	?>

	<div class="hide-if-js">
		<form id="nearbywp-form" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
			<input id="nearbywp-location" class="regular-text" type="text" name="nearbywp-location" />
			<?php submit_button( __( 'Submit' ), 'primary', 'nearbywp-submit', false ); ?>
		</form>
	</div>
	<div id="nearbywp" class="hide-if-no-js">
		<span class="spinner is-active"></span>
		<?php esc_html_e( 'Loading&hellip;' ); ?>
	</div>

	<script id="tmpl-nearbywp" type="text/template">
		<div class="activity-block">
			<h2>
				<?php printf( __( 'Attend an upcoming event near %s' ), '<strong>{{{ data.location }}}</strong>' ); ?>
			</h2>
			<button id="nearbywp-toggle" class="button-link">
				<?php esc_html_e( 'Not your location?' ); ?>
			</button>
		</div>
		<ul class="activity-block">
			<# _.each( data.events, function( event ) { #>
				<li class="event-{{ event.type }}">
					<span class="event-icon"></span>
					<h3 class="event-title"><a href="{{ event.url }}">{{ event.title }}</a></h3>
					<span class="event-city description">{{ event.city }}</span>
					<span class="event-date description">{{ event.date }}</span>
				</li>
			<# } ) #>
		</ul>
		<p><?php esc_html_e( 'Looking for something closer?' ); ?></p>
	</script>

	<?php
}
