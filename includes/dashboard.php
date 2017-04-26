<?php

defined( 'WPINC' ) || die();

/**
 * Register Dashboard widget
 */
function nearbywp_register_dashboard_widgets() {
	wp_add_dashboard_widget(
		'nearbywp_dashboard_events',
		esc_html__( 'WordPress Events and News', 'nearby-wp-events' ),
		'nearbywp_render_dashboard_widget'
	);

	// Remove WordPress News because we'll incorporate its contents into the new widget.
	remove_meta_box( 'dashboard_primary', get_current_screen(), 'side' );
}

/**
 * Render callback for the Dashboard widget
 */
function nearbywp_render_dashboard_widget() {
	?>

	<div class="hide-if-js notice notice-error inline">
		<p><?php esc_html_e( 'This widget requires JavaScript.', 'nearby-wp-events' ); ?></p>
	</div>

	<div id="nearbywp" class="hide-if-no-js nearbywp">
		<div class="activity-block">
			<p>
				<span id="nearbywp-location-message"><?php esc_html_e( 'Loading&hellip;', 'nearby-wp-events' ); ?></span>
				<button id="nearbywp-toggle" class="button-link nearbywp-toggle hidden" aria-label="<?php esc_attr_e( 'Edit location', 'nearby-wp-events' ); ?>" aria-expanded="false">
					<span class="dashicons dashicons-edit" aria-hidden="true"></span>
				</button>
			</p>

			<form id="nearbywp-form" class="nearbywp-form" aria-hidden="true" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
				<label for="nearbywp-location" >
					<?php esc_html_e( 'City name:', 'nearby-wp-events' ); ?>
				</label>
				<?php /* translators: Replace with the name of a city in your locale. Use only the city name itself, without any region or country. Use the endonym instead of the English name. */ ?>
				<input id="nearbywp-location" class="regular-text" type="text" name="nearbywp-location" placeholder="<?php esc_attr_e( 'Cincinnati', 'nearby-wp-events' ); ?>" />

				<?php submit_button( esc_html__( 'Submit', 'nearby-wp-events' ), 'primary', 'nearbywp-submit', false ); ?>

				<span class="spinner"></span>
			</form>
		</div>

		<ul id="nearbywp-results" class="activity-block last"></ul>
	</div>

	<div id="dashboard_primary" class="hide-if-no-js">
		<div class="inside">
			<?php wp_dashboard_primary(); ?>
		</div>
	</div>

	<p class="nearbywp-footer">
		<a href="https://make.wordpress.org/community/meetups-landing-page">
			<?php esc_html_e( 'Meetups', 'nearby-wp-events' ); ?> <span class="dashicons dashicons-external"></span>
		</a>

		|

		<a href="https://central.wordcamp.org/schedule/">
			<?php esc_html_e( 'WordCamps', 'nearby-wp-events' ); ?> <span class="dashicons dashicons-external"></span>
		</a>

		|

		<?php // translators: If a Rosetta site exists (e.g. https://es.wordpress.org/news/), then use that. Otherwise, leave untranslated. ?>
		<a href="<?php esc_html_e( 'https://wordpress.org/news/', 'nearby-wp-events' ); ?>">
			<?php esc_html_e( 'News', 'nearby-wp-events' ); ?> <span class="dashicons dashicons-external"></span>
		</a>
	</p>

	<?php
}

/**
 * JS templates for the Dashboard widget
 */
function nearbywp_render_js_templates() {
	$inline_script_data = nearbywp_get_inline_script_data();

	?>

	<script id="tmpl-nearbywp-location" type="text/template">

		<# if ( data.location.description ) { #>
			<?php printf(
				wp_kses(
					/* translators: %s is a placeholder for the name of a city. */
					__( 'Attend an upcoming event near <strong>%s</strong>', 'nearby-wp-events' ),
					wp_kses_allowed_html( 'data' )
				),
				'{{ data.location.description }}'
			); ?>

		<# } else if ( data.unknownCity ) { #>
			<?php printf(
				wp_kses(
					$inline_script_data['i18n']['couldNotLocateCity'],
					wp_kses_allowed_html( 'data' )
				),
				'{{data.unknownCity}}'
			); ?>

		<# } else if ( data.error ) { #>
			<?php echo esc_html( $inline_script_data['i18n']['errorOccurredPleaseTryAgain'] ); ?>

		<# } else { #>
			<?php esc_html_e( 'Enter your closest city name to find nearby events', 'nearby-wp-events' ); ?>
		<# } #>

	</script>

	<script id="tmpl-nearbywp-events" type="text/template">

		<# if ( data.location.description ) { #>
			<# if ( data.events.length ) { #>

				<# _.each( data.events, function( event ) { #>
					<li class="event event-{{ event.type }} wp-clearfix">
						<div class="event-info">
							<div class="dashicons event-icon" aria-hidden="true"></div>
							<div class="event-info-inner">
								<a class="event-title" href="{{ event.url }}">{{ event.title }}</a>
								<span class="event-city">{{ event.location.location }}</span>
							</div>
						</div>
						<div class="event-date-time">
							<span class="event-date">{{ event.formatted_date }}</span>
							<# if ( 'meetup' === event.type ) { #>
								<span class="event-time">{{ event.formatted_time }}</span>
							<# } #>
						</div>
					</li>
				<# } ) #>

			<# } else { #>

				<li class="event-none">
					<?php printf(
						wp_kses(
							/* translators: Replace the URL if a locale-specific one exists */
							__( 'There aren\'t any events scheduled near %s at the moment. Would you like to <a href="https://make.wordpress.org/community/handbook/meetup-organizer/welcome/">organize one</a>?', 'nearby-wp-events' ),
							wp_kses_allowed_html( 'data' )
						),
						'{{data.location.description}}'
					); ?>
				</li>

			<# } #>
		<# } #>

	</script>

	<?php
}
