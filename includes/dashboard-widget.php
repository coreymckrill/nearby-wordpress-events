<?php
/**
 * Templates for the Dashboard widget.
 *
 * @package Nearby WordPress Events
 */

defined( 'WPINC' ) || die();

/**
 * Render callback for the Dashboard widget
 */
function nearbywp_render_dashboard_widget() {
	?>

	<div class="hide-if-js notice notice-error notice-alt inline">
		<p><?php esc_html_e( 'This widget requires JavaScript.', 'nearby-wp-events' ); ?></p>
	</div>

	<div id="nearbywp" class="hide-if-no-js nearbywp">
		<span class="spinner is-active"></span>
		<?php esc_html_e( 'Loading&hellip;', 'nearby-wp-events' ); ?>
	</div>

	<div id="dashboard_primary">
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
	?>

	<script id="tmpl-nearbywp" type="text/template">
		<div class="activity-block <# if ( ! data.location.description ) print( 'last' ) #>">
			<# if ( data.location.description ) { #>

				<p>
					<?php printf(
						wp_kses(
							/* translators: %s is a placeholder for the name of a city. */
							__( 'Attend an upcoming event near <strong>%s</strong>', 'nearby-wp-events' ),
							wp_kses_allowed_html( 'data' )
						),
						'{{ data.location.description }}'
					); ?>
					<button id="nearbywp-toggle" class="button-link nearbywp-toggle" aria-label="<?php esc_attr_e( 'Edit location', 'nearby-wp-events' ); ?>" aria-expanded="false">
						<span class="dashicons dashicons-edit" aria-hidden="true"></span>
					</button>
				</p>

			<# } else if ( data.unknown_city ) { #>

				<p>
					<?php printf(
						/*
						 * The Events API works for most city names, but there are a lot of edge cases that are
						 * difficult to solve, especially with ideographic languages. We can't give generic
						 * instructions to the user very well, because the edge cases are different for each
						 * locale. The translator is in the best position to determine appropriate examples for
						 * their locale.
						 *
						 * We should encourage the use of endonyms as much as possible, to provide the best
						 * experience for the majority of users, for whom English is not their first language.
						 */
						wp_kses(
							/* translators: %s is the name of the city we couldn't locate. Replace the examples with variations of cities in your locale that return results. Use endonyms whenever possible. */
							__( "We couldn't locate <strong><em>%s</em></strong>. Please try another nearby city, or try different variations of <strong><em>%s</em></strong>. For example: <em>London; London, GB; United Kingdom</em>.", 'nearby-wp-events' ),
							wp_kses_allowed_html( 'data' )
						),
						'{{data.unknown_city}}',
						'{{data.unknown_city}}'
					); ?>
				</p>

			<# } else if ( data.error ) { #>

				<p>
					<?php printf(
						wp_kses(
							/* translators: %s is the detailed error message. */
							__( 'An error occured while trying to retrieve events. Please try again. <code>[%s]</code>', 'nearby-wp-events' ),
							wp_kses_allowed_html( 'data' )
						),
						'{{data.error}}'
					); ?>
				</p>

			<# } else { #>

				<p><?php esc_html_e( 'Enter your closest city name to find nearby events:', 'nearby-wp-events' ); ?></p>

			<# } #>

			<form id="nearbywp-form" class="nearbywp-form" aria-hidden="<# print( data.location.description ? 'true' : 'false' ); #>" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
				<label for="nearbywp-location" >
					<?php _e( 'City name:', 'nearby-wp-events' ); ?>
				</label>
				<?php /* translators: Replace with the name of a city in your locale. Use only the city name itself, without any region or country. Use the endonym instead of the English name. */ ?>
				<input id="nearbywp-location" class="regular-text" type="text" name="nearbywp-location" placeholder="<?php esc_attr_e( 'Cincinnati', 'nearby-wp-events' ); ?>" />

				<?php submit_button( __( 'Submit', 'nearby-wp-events' ), 'primary', 'nearbywp-submit', false ); ?>

				<span class="spinner"></span>
			</form>
		</div>

		<# if ( data.location.description ) { #>
			<ul id="nearbywp-results" class="activity-block last">
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
			</ul>
		<# } #>
	</script>

	<?php
}
add_action( 'admin_print_footer_scripts-index.php', 'nearbywp_render_js_templates' );
