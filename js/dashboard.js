wp.NearbyWP = wp.NearbyWP || {};

jQuery( function( $ ) {
	'use strict';

	var app = wp.NearbyWP.Dashboard = {
		initialized: false,

		/**
		 * Main entry point
		 */
		init: function() {
			if ( app.initialized ) {
				return;
			}

			var $container = $( '#nearbywp' );

			if ( nearbyWPData.cachedData.hasOwnProperty( 'location' ) && nearbyWPData.cachedData.hasOwnProperty( 'events' ) ) {
				app.renderEventsTemplate( nearbyWPData.cachedData );
			} else {
				app.getEvents();
			}

			$container.on( 'click', '#nearbywp-toggle', function() {
				var $toggle  = $( '#nearbywp-toggle' ),
					$form    = $( '#nearbywp-form' ),
					expanded = $toggle.attr( 'aria-expanded' );

				if ( 'true' == expanded ) { // Strict comparison doesn't work in this case.
					$toggle.attr( 'aria-expanded', false );
					$form.attr( 'aria-hidden', true );
				} else {
					$toggle.attr( 'aria-expanded', true );
					$form.attr( 'aria-hidden', false );
				}
			});

			$container.on( 'submit', '#nearbywp-form', function( event ) {
				event.preventDefault();

				app.getEvents( {
					location: $( '#nearbywp-location' ).val()
				} )
			});

			app.initialized = true;
		},

		/**
		 * Send Ajax request to fetch events for the widget
		 *
		 * @param data
		 */
		getEvents: function( data ) {
			var $spinner = $( '#nearbywp-form' ).children( '.spinner' );

			data          = data || {};
			data._wpnonce = nearbyWPData.nonce;
			data.timezone = window.Intl ? window.Intl.DateTimeFormat().resolvedOptions().timeZone : '';

			$spinner.addClass( 'is-active' );

			wp.ajax.post( 'nearbywp_get_events', data )
				.always( function() {
					$spinner.removeClass( 'is-active' );
				})
				.done( function( events ) {
					if ( events.hasOwnProperty( 'error' ) && 'no_location_available' === events.error ) {
						events.unknown_city = data.location;
					}

					app.renderEventsTemplate( events );
				})
				.fail( function( failedResponse ) {
					var events = { 'location' : false };

					if ( 'string' === typeof failedResponse ) {
						events.error = failedResponse;
					} else if ( failedResponse.hasOwnProperty( 'statusText' ) ) {
						events.error = failedResponse.statusText;
					} else {
						events.error = 'Unknown error';
					}

					app.renderEventsTemplate( events );
				});
		},

		/**
		 * Render the template for the Events section of the Events & News widget
		 *
		 * @param {Object} data
		 */
		renderEventsTemplate : function( data ) {
			var template = wp.template( 'nearbywp' );

			$( '#nearbywp' ).html( template( data ) );
		}
	};

	if ( $( '#nearbywp' ).is( ':visible' ) ) {
		app.init();
	} else {
		$( document ).on( 'postbox-toggled', function( event, postbox ) {
			var $postbox = $( postbox );

			if ( 'nearbywp_dashboard_events' === $postbox.attr( 'id' ) && $postbox.is( ':visible' ) ) {
				app.init();
			}
		});
	}
});
