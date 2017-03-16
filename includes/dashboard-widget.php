<?php

defined( 'WPINC' ) or die();

/**
 * Render callback for the Dashboard widget
 */
function nearbywp_render_dashboard_widget() {
	?>

	<div id="nearbywp" class="hide-if-no-js nearbywp">
		<span class="spinner is-active"></span>
		<?php esc_html_e( 'Loading&hellip;' ); ?>
	</div>

	<div class="hide-if-js">
		<?php esc_html_e( 'This widget requires JavaScript.' ); ?>
	</div>

	<div id="dashboard_primary">
		<div class="inside">
			<?php wp_dashboard_primary(); ?>
		</div>
	</div>

	<p class="nearbywp-footer">
		<a href="<?php esc_html_e( 'https://make.wordpress.org/community/meetups-landing-page' ); ?>">
			<?php esc_html_e( 'Meetups' ); ?> <span class="dashicons dashicons-external"></span>
		</a>

		|

		<a href="<?php esc_html_e( 'https://central.wordcamp.org/schedule/' ); ?>">
			<?php esc_html_e( 'WordCamps' ); ?> <span class="dashicons dashicons-external"></span>
		</a>

		|

		<a href="<?php esc_html_e( 'https://wordpress.org/news/' ); ?>">
			<?php esc_html_e( 'News' ); ?> <span class="dashicons dashicons-external"></span>
		</a>
	</p>

	<?php
}

/**
 * JS templates for the Dashboard widget
 */
function nearbywp_render_js_templates() {
	?>

	<script id="tmpl-nearbywp" type="text/template">
		<div class="activity-block <# if ( ! data.location.description ) print( 'last' ) #>">
			<# if ( data.location.description ) { #>

				<p>
					<?php esc_html_e( 'Attend an upcoming event near' ); ?>
					<button id="nearbywp-toggle" class="button-link nearbywp-toggle">
						<strong>{{{ data.location.description }}}</strong>
						<span class="dashicons dashicons-edit" aria-hidden="true"></span>
					</button>
				</p>

			<# } else { #>

				<p><?php esc_html_e( 'Enter your closest city to find nearby events' ); ?></p>

			<# } #>

			<form id="nearbywp-form" class="nearbywp-form <# if ( data.location.description ) print( 'hide' ) #>" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
				<label for="nearbywp-location" class="screen-reader-text">
					<?php esc_html_e( 'Enter a nearby city' ); ?>
				</label>
				<input id="nearbywp-location" class="regular-text" type="text" name="nearbywp-location" placeholder="<?php esc_attr_e( 'City' ); ?>" />

				<?php submit_button( __( 'Submit' ), 'primary', 'nearbywp-submit', false ); ?>

				<button id="nearbywp-cancel" class="button button-secondary <# if ( ! data.location.description ) print( 'hide' ) #>" type="button">
					<?php esc_html_e( 'Cancel' ); ?>
				</button>

				<span class="spinner"></span>
			</form>
		</div>

		<# if ( data.location.description ) { #>
			<ul id="nearbywp-results" class="activity-block last">
				<# if ( data.events.length ) { #>

					<# _.each( data.events, function( event ) { #>
						<li class="event-{{ event.type }}">
							<div class="dashicons event-icon" aria-hidden="true"></div>
							<div class="event-date">{{ event.date }}</div>
							<div class="event-info">
								<a class="event-title" href="{{ event.url }}">{{ event.title }}</a>
								<span class="event-city">{{ event.location.location }}</span>
							</div>
						</li>
					<# } ) #>

				<# } else { #>

					<li class="event-none">
						<?php printf(
							__( 'There aren\'t any events scheduled near %s at the moment. Would you like to <a href="%s">organize one</a>?' ),
							'{{data.location.description}}',
							'https://make.wordpress.org/community/handbook/meetup-organizer/welcome/'
						); ?>
					</li>

				<# } #>
			</ul>
		<# } #>
	</script>

	<?php
}
add_action( 'admin_print_footer_scripts-index.php', 'nearbywp_render_js_templates' );
