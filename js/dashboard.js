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
					$form.find( 'input[name="nearbywp-location"]' ).focus();
				}
			});

			$container.on( 'submit', '#nearbywp-form', function( event ) {
				event.preventDefault();

				app.getEvents( {
					location: $( '#nearbywp-location' ).val()
				} )
			});

			if ( nearbyWPData.cachedData.hasOwnProperty( 'location' ) && nearbyWPData.cachedData.hasOwnProperty( 'events' ) ) {
				app.renderEventsTemplate( nearbyWPData.cachedData );
			} else {
				app.getEvents();
			}

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
						events.unknownCity = data.location;
					}

					app.renderEventsTemplate( events );

					// Speak the error after the template has been rendered
					if ( events.hasOwnProperty( 'unknown_city' ) ) {
						wp.a11y.speak( nearbyWPData.i18n.couldNotLocateCity.replace( /%\d\$s/g, events.unknown_city ) );
					}
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
					wp.a11y.speak( nearbyWPData.i18n.errorOccurredPleaseTryAgain.replace( /%s/, events.error ) );
				});
		},

		/**
		 * Render the template for the Events section of the Events & News widget
		 *
		 * @param {Object} data
		 */
		renderEventsTemplate : function( data ) {
			var locationTemplate = wp.template( 'nearbywp-location' ),
				eventsTemplate   = wp.template( 'nearbywp-events' ),
				$toggle          = $( '#nearbywp-toggle' );

			$toggle.removeClass( 'hidden' );

			$( '#nearbywp-location-message' ).html( locationTemplate( data ) );
			$( '#nearbywp-results' ).html( eventsTemplate( data ) );

			if ( ! data.location.description ) {
				$toggle.trigger( 'click' );
			}
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
