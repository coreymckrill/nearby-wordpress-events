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
	<div id="nearbywp" class="hide-if-no-js nearbywp">
		<span class="spinner is-active"></span>
		<?php esc_html_e( 'Loading&hellip;' ); ?>
	</div>

	<script id="tmpl-nearbywp" type="text/template">
		<div class="activity-block">
			<h2>
				<?php printf( __( 'Attend an upcoming event near %s' ), '<strong>{{{ data.location }}}</strong>' ); ?>
			</h2>
			<button id="nearbywp-toggle" class="button-link nearbywp-toggle">
				<?php esc_html_e( 'Not your location?' ); ?>
			</button>
		</div>
		<ul class="activity-block">
			<# _.each( data.events, function( event ) { #>
				<li class="event-{{ event.type }}">
					<div class="dashicons event-icon"></div>
					<div class="event-info">
						<a class="event-title" href="{{ event.url }}">{{ event.title }}</a>
						<span class="event-city">{{ event.city }}</span>
					</div>
					<div class="event-date">{{ event.date }}</div>
				</li>
			<# } ) #>
		</ul>
		<p class="nearbywp-footer"><?php esc_html_e( 'Looking for something closer?' ); ?></p>
	</script>

	<?php
}
