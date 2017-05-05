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
 *
 * @since 4.8.0
 */
function nearbywp_render_dashboard_widget() {
	$inline_script_data = nearbywp_get_inline_script_data();

	?>

	<div class="nearbywp-errors notice notice-error inline hide-if-js">
		<p class="hide-if-js">
			<?php esc_html_e( 'This widget requires JavaScript.', 'nearby-wp-events' ); ?>
		</p>

		<p class="nearbywp-error-occurred" aria-hidden="true">
			<?php echo esc_html( $inline_script_data['l10n']['error_occurred_please_try_again'] ); ?>
		</p>

		<p class="nearbywp-could-not-locate" aria-hidden="true"></p>
	</div>

	<span class="nearbywp-loading hide-if-no-js">
		<?php esc_html_e( 'Loading&hellip;', 'nearby-wp-events' ); ?>
	</span>

	<?php
	/*
	 * Hide the main element when the page first loads, because the content
	 * won't be ready until wp.NearbyWP.Dashboard.renderEventsTemplate() has
	 * run.
	 *
	 * @todo update the name of the class if it changes during the merge to Core
	 */
	?>
	<div id="nearbywp" class="nearbywp" aria-hidden="true">
		<div class="activity-block">
			<p>
				<span id="nearbywp-location-message"></span>

				<button id="nearbywp-toggle-location" class="button-link nearbywp-toggle-location" aria-label="<?php esc_attr_e( 'Edit city', 'nearby-wp-events' ); ?>" aria-expanded="false">
					<span class="dashicons dashicons-edit"></span>
				</button>
			</p>

			<form id="nearbywp-form" class="nearbywp-form" aria-hidden="true" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
				<label for="nearbywp-location">
					<?php esc_html_e( 'City name:', 'nearby-wp-events' ); ?>
				</label>
				<?php /* translators: Replace with the name of a city in your locale that shows events. Use only the city name itself, without any region or country. Use the endonym instead of the English name. */ ?>
				<input id="nearbywp-location" class="regular-text" type="text" name="nearbywp-location" placeholder="<?php esc_attr_e( 'Cincinnati', 'nearby-wp-events' ); ?>" />

				<?php submit_button( esc_html__( 'Submit', 'nearby-wp-events' ), 'secondary', 'nearbywp-submit', false ); ?>

				<button class="nearbywp-cancel button button-link" type="button" aria-expanded="false">
					<?php esc_html_e( 'Cancel', 'nearby-wp-events' ); ?>
				</button>

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
		<a href="https://make.wordpress.org/community/meetups-landing-page" target="_blank">
			<?php esc_html_e( 'Meetups', 'nearby-wp-events' ); ?> <span class="dashicons dashicons-external"></span>
		</a>

		|

		<a href="https://central.wordcamp.org/schedule/" target="_blank">
			<?php esc_html_e( 'WordCamps', 'nearby-wp-events' ); ?> <span class="dashicons dashicons-external"></span>
		</a>

		|

		<?php // translators: If a Rosetta site exists (e.g. https://es.wordpress.org/news/), then use that. Otherwise, leave untranslated. ?>
		<a href="<?php esc_html_e( 'https://wordpress.org/news/', 'nearby-wp-events' ); ?>" target="_blank">
			<?php esc_html_e( 'News', 'nearby-wp-events' ); ?> <span class="dashicons dashicons-external"></span>
		</a>
	</p>

	<?php
}

/**
 * JS templates for the Dashboard widget
 *
 * @since 4.8.0
 */
function nearbywp_render_js_templates() {
	$inline_script_data = nearbywp_get_inline_script_data();

	?>

	<script id="tmpl-nearbywp-attend-event-near" type="text/template">
		<?php printf(
			wp_kses(
				/* translators: %s is a placeholder for the name of a city. */
				__( 'Attend an upcoming event near <strong>%s</strong>', 'nearby-wp-events' ),
				wp_kses_allowed_html( 'data' )
			),
			'{{ data.location.description }}'
		); ?>
	</script>

	<script id="tmpl-nearbywp-could-not-locate" type="text/template">
		<?php printf(
			wp_kses(
				$inline_script_data['l10n']['could_not_locate_city'],
				wp_kses_allowed_html( 'data' )
			),
			'{{data.unknownCity}}'
		); ?>
	</script>

	<script id="tmpl-nearbywp-event-list" type="text/template">
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
	</script>

	<script id="tmpl-nearbywp-no-upcoming-events" type="text/template">
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
	</script>

	<?php
}
