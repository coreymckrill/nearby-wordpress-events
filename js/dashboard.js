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

			$( '.nearbywp-errors' ).attr( 'aria-hidden', true );
			$( '.nearbywp-errors' ).removeClass( 'hide-if-js' );

			$container.on( 'click', '#nearbywp-toggle', app.toggleLocationForm );

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
		 * Toggle the visibility of the Edit Location form
		 *
		 * @param {event|string} action 'show' or 'hide' to specify a state;
		 *                              Or an event object to flip between states
		 */
		toggleLocationForm : function( action ) {
			var $toggleButton = $( '#nearbywp-toggle' ),
			    $form         = $( '#nearbywp-form' );

			if ( 'object' === typeof action ) {
				// Strict comparison doesn't work in this case.
				action = 'true' == $toggleButton.attr( 'aria-expanded' ) ? 'hide' : 'show';
			}

			if ( 'hide' === action ) {
				$toggleButton.attr( 'aria-expanded', false );
				$form.attr( 'aria-hidden', true );
			} else {
				$toggleButton.attr( 'aria-expanded', true );
				$form.attr( 'aria-hidden', false );
				$form.find( 'input[name="nearbywp-location"]' ).focus();
			}
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
					if ( 'no_location_available' === events.error ) {
						if ( data.location ) {
							events.unknownCity = data.location;
						} else {
							/*
							 * No location was passed, which means that this was an automatic query
							 * based on IP, locale, and timezone. Since the user didn't initiate it,
							 * it should fail silently. Otherwise, the error could confuse and/or
							 * annoy them.
							 */
							delete events.error;
						}
					}

					app.renderEventsTemplate( events );

					// Speak the error after the template has been rendered
					if ( events.hasOwnProperty( 'unknown_city' ) ) {
						wp.a11y.speak( nearbyWPData.i18n.couldNotLocateCity.replace( /%\d\$s/g, events.unknown_city ) );
					}
				})
				.fail( function( failedResponse ) {
					app.renderEventsTemplate( {
						'location' : false,
						'error'    : true
					} );

					wp.a11y.speak( nearbyWPData.i18n.errorOccurredPleaseTryAgain );
				});
		},

		/**
		 * Render the template for the Events section of the Events & News widget
		 *
		 * @param {Object} data
		 */
		renderEventsTemplate : function( data ) {
			var template,
			    elementVisibility,
			    searchHasFocus   = 'nearbywp-location' === document.activeElement.getAttribute( 'name' ),
			    $locationMessage = $( '#nearbywp-location-message' ),
			    $results         = $( '#nearbywp-results' );

			/*
			 * Hide all toggleable elements by default, to keep the logic simple.
			 * Otherwise, each block below would have to turn hide everything that
			 * could have been shown at an earlier point.
			 */
			elementVisibility = {
				'.nearbywp'                  : true,  // This is off when the page first loads, because the content isn't ready yet
				'.nearbywp-loading'          : false,
				'.nearbywp-errors'           : false,
				'.nearbywp-error-occurred'   : false,
				'.nearbywp-could-not-locate' : false,
				'#nearbywp-location-message' : false,
				'#nearbywp-toggle'           : false,
				'#nearbywp-results'          : false
			};

			if ( data.location.description ) {
				template = wp.template( 'nearbywp-attend-event-near' );
				$locationMessage.html( template( data ) );

				if ( data.events.length ) {
					template = wp.template( 'nearbywp-event-list' );
					$results.html( template( data ) );
				} else {
					template = wp.template( 'nearbywp-no-upcoming-events' );
					$results.html( template( data ) );
				}

				elementVisibility['#nearbywp-location-message'] = true;
				elementVisibility['#nearbywp-toggle']           = true;
				elementVisibility['#nearbywp-results']          = true;

			} else if ( data.unknownCity ) {
				template = wp.template( 'nearbywp-could-not-locate' );
				$( '.nearbywp-could-not-locate' ).html( template( data ) );

				elementVisibility['.nearbywp-errors']           = true;
				elementVisibility['.nearbywp-could-not-locate'] = true;

			} else if ( data.error && searchHasFocus ) {
				// Only show this error if it was a user-initiated request (i.e., if it has a location).
				// Don't show it for automatic requests (when no location is saved)

				elementVisibility['.nearbywp-errors']         = true;
				elementVisibility['.nearbywp-error-occurred'] = true;

			} else {
				$locationMessage.text( nearbyWPData.i18n.enterClosestCity );

				elementVisibility['#nearbywp-location-message'] = true;
				elementVisibility['#nearbywp-toggle']           = true;
			}

			// Set the visibility of toggleable elements
			_.each( elementVisibility, function( isVisible, element ) {
				$( element ).attr( 'aria-hidden', ! isVisible );
			} );

			$( '#nearbywp-toggle' ).attr( 'aria-expanded', elementVisibility['toggle'] );

			if ( ! searchHasFocus && data.location.description ) {
				app.toggleLocationForm( 'hide' );
			} else {
				app.toggleLocationForm( 'show' );
			}
		}
	};

	if ( $( '#nearbywp_dashboard_events' ).is( ':visible' ) ) {
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
