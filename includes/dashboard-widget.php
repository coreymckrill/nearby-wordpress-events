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

	<?php wp_dashboard_primary(); ?>

	<p class="nearbywp-footer">
		<a href="<?php esc_html_e( 'https://www.meetup.com/pro/wordpress/' ); ?>"><?php esc_html_e( 'Meetups' ); ?> <span class="dashicons dashicons-external"></span></a> | <a href="<?php esc_html_e( 'https://central.wordcamp.org/schedule/' ); ?>"><?php esc_html_e( 'WordCamps' ); ?> <span class="dashicons dashicons-external"></span></a> | <a href="<?php esc_html_e( 'https://wordpress.org/news/' ); ?>"><?php esc_html_e( 'News' ); ?> <span class="dashicons dashicons-external"></span></a>
	</p>
	<?php
}

/**
 * JS templates for the Dashboard widget
 */
function nearbywp_render_js_templates() {
	?>
	<script id="tmpl-nearbywp" type="text/template">
		<div class="activity-block">
			<p>
                <# if ( data.events.length >= 1 ) { #>
				<?php printf( __( 'Attend an upcoming event near %s' ), '<strong>{{{ data.location.description }}}</strong> ' ); ?>
                <# } #>
			</p>
			<button id="nearbywp-toggle" class="button-link nearbywp-toggle">
                <# if ( data.events.length < 1 ) { #>
	                <?php esc_html_e( 'Find nearby events' ); ?>
                <# } else { #>
                    <span class="screen-reader-text"><?php esc_html_e( 'Change location' ); ?></span>
                <# } #>
                <span class="dashicons dashicons-edit" aria-hidden="true"></span>
			</button>
			<form id="nearbywp-form" class="nearbywp-form" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
				<!-- <label>Edit location</label> -->
				<input id="nearbywp-location" class="regular-text" type="text" name="nearbywp-location" placeholder="Edit location" />
				<?php submit_button( __( 'Submit' ), 'primary', 'nearbywp-submit', false ); ?>
				<button id="nearbywp-cancel" class="button button-secondary" type="button"><?php esc_html_e( 'Cancel' ); ?></button>
				<span class="spinner"></span>
			</form>
		</div>
		<ul id="nearbywp-results" class="activity-block">
			<# if ( data.events.length ) { #>
				<# _.each( data.events, function( event ) { #>
					<li class="event-{{ event.type }}">
						<div class="dashicons event-icon"></div>
						<div class="event-date">{{ event.date }}</div>
						<div class="event-info">
							<a class="event-title" href="{{ event.url }}">{{ event.title }}</a>
							<span class="event-city">{{ event.location.location }}</span>
						</div>
					</li>
				<# } ) #>
			<# } else { #>
				<li class="event-none">
					<?php esc_html_e( 'No events found.' ); ?>
				</li>
			<# } #>
		</ul>
	</script>
<?php
}

add_action( 'admin_print_footer_scripts-index.php', 'nearbywp_render_js_templates' );