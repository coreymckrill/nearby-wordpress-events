wp.NearbyWP = wp.NearbyWP || {};

jQuery( function( $ ) {
	'use strict';

	var app = wp.NearbyWP.Dashboard = {
		initialized: false,

		/**
		 * Main entry point
		 *
		 * @since 4.8.0
		 */
		init: function() {
			if ( app.initialized ) {
				return;
			}

			var $container = $( '#nearbywp' );

			/*
			 * When JavaScript is disabled, the errors container is shown, so
			 * that "This widget requires Javascript" message can be seen.
			 *
			 * When JS is enabled, the container is hidden at first, and then
			 * revealed during the template rendering, if there actually are
			 * errors to show.
			 *
			 * The display indicator switches from `hide-if-js` to `aria-hidden`
			 * here in order to maintain consistency with all the other fields
			 * that key off of `aria-hidden` to determine their visibility.
			 * `aria-hidden` can't be used initially, because there would be no
			 * way to set it to false when JavaScript is disabled, which would
			 * prevent people from seeing the "This widget requires JavaScript"
			 * message.
			 */
			$( '.nearbywp-errors' )
				.attr( 'aria-hidden', true )
				.removeClass( 'hide-if-js' );

			$container.on( 'click', '#nearbywp-toggle-location', app.toggleLocationForm );
			$container.on( 'click', '.nearbywp-cancel', app.toggleLocationForm );

			$container.on( 'submit', '#nearbywp-form', function( event ) {
				event.preventDefault();

				app.getEvents( {
					location: $( '#nearbywp-location' ).val()
				} )
			});

			if ( nearbyWPData.cachedData.location && nearbyWPData.cachedData.events ) {
				app.renderEventsTemplate( nearbyWPData.cachedData, 'app' );
			} else {
				app.getEvents();
			}

			app.initialized = true;
		},

		/**
		 * Toggle the visibility of the Edit Location form
		 *
		 * @since 4.8.0
		 *
		 * @param {event|string} action 'show' or 'hide' to specify a state;
		 *                              Or an event object to flip between states
		 */
		toggleLocationForm : function( action ) {
			var $toggleButton = $( '#nearbywp-toggle-location' ),
			    $cancelButton = $( '.nearbywp-cancel' ),
			    $form         = $( '#nearbywp-form' );

			if ( 'object' === typeof action ) {
				// Strict comparison doesn't work in this case.
				action = 'true' == $toggleButton.attr( 'aria-expanded' ) ? 'hide' : 'show';
			}

			if ( 'hide' === action ) {
				$toggleButton.attr( 'aria-expanded', false );
				$cancelButton.attr( 'aria-expanded', false );
				$form.attr( 'aria-hidden', true );
			} else {
				$toggleButton.attr( 'aria-expanded', true );
				$cancelButton.attr( 'aria-expanded', true );
				$form.attr( 'aria-hidden', false );
			}
		},

		/**
		 * Send Ajax request to fetch events for the widget
		 *
		 * @since 4.8.0
		 *
		 * @param {object} requestParams
		 */
		getEvents: function( requestParams ) {
			var initiatedBy,
			    $spinner = $( '#nearbywp-form' ).children( '.spinner' );

			requestParams          = requestParams || {};
			requestParams._wpnonce = nearbyWPData.nonce;
			requestParams.timezone = window.Intl ? window.Intl.DateTimeFormat().resolvedOptions().timeZone : '';

			initiatedBy = requestParams.location ? 'user' : 'app';

			$spinner.addClass( 'is-active' );

			wp.ajax.post( 'nearbywp_get_events', requestParams )
				.always( function() {
					$spinner.removeClass( 'is-active' );
				})
				.done( function( successfulResponse ) {
					if ( 'no_location_available' === successfulResponse.error ) {
						if ( requestParams.location ) {
							successfulResponse.unknownCity = requestParams.location;
						} else {
							/*
							 * No location was passed, which means that this was an automatic query
							 * based on IP, locale, and timezone. Since the user didn't initiate it,
							 * it should fail silently. Otherwise, the error could confuse and/or
							 * annoy them.
							 */
							delete successfulResponse.error;
						}
					}

					app.renderEventsTemplate( successfulResponse, initiatedBy );
				})
				.fail( function( failedResponse ) {
					app.renderEventsTemplate( {
						'location' : false,
						'error'    : true
					}, initiatedBy );
				});
		},

		/**
		 * Render the template for the Events section of the Events & News widget
		 *
		 * @since 4.8.0
		 *
		 * @param {Object} templateParams The various parameters that will get passed to wp.template
		 * @param {string} initiatedBy    'user' to indicate that this was triggered manually by the user;
		 *                                'app' to indicate it was triggered automatically by the app itself.
		 */
		renderEventsTemplate : function( templateParams, initiatedBy ) {
			var template,
			    elementVisibility,
			    $locationMessage = $( '#nearbywp-location-message' ),
			    $results         = $( '#nearbywp-results' );

			/*
			 * Hide all toggleable elements by default, to keep the logic simple.
			 * Otherwise, each block below would have to turn hide everything that
			 * could have been shown at an earlier point.
			 *
			 * The exception to that is that the .nearbywp container. It's hidden
			 * when the page is first loaded, because the content isn't ready yet,
			 * but once we've reached this point, it should always be shown.
			 */
			elementVisibility = {
				'.nearbywp'                  : true,
				'.nearbywp-loading'          : false,
				'.nearbywp-errors'           : false,
				'.nearbywp-error-occurred'   : false,
				'.nearbywp-could-not-locate' : false,
				'#nearbywp-location-message' : false,
				'#nearbywp-toggle-location'  : false,
				'#nearbywp-results'          : false
			};

			/*
			 * Determine which templates should be rendered and which elements
			 * should be displayed
			 */
			if ( templateParams.location.description ) {
				template = wp.template( 'nearbywp-attend-event-near' );
				$locationMessage.html( template( templateParams ) );

				if ( templateParams.events.length ) {
					template = wp.template( 'nearbywp-event-list' );
					$results.html( template( templateParams ) );
				} else {
					template = wp.template( 'nearbywp-no-upcoming-events' );
					$results.html( template( templateParams ) );
				}
				wp.a11y.speak( nearbyWPData.l10n.city_updated.replace( /%s/g, templateParams.location.description ) );

				elementVisibility['#nearbywp-location-message'] = true;
				elementVisibility['#nearbywp-toggle-location']  = true;
				elementVisibility['#nearbywp-results']          = true;

			} else if ( templateParams.unknownCity ) {
				template = wp.template( 'nearbywp-could-not-locate' );
				$( '.nearbywp-could-not-locate' ).html( template( templateParams ) );
				wp.a11y.speak( nearbyWPData.l10n.could_not_locate_city.replace( /%s/g, templateParams.unknownCity ) );

				elementVisibility['.nearbywp-errors']           = true;
				elementVisibility['.nearbywp-could-not-locate'] = true;

			} else if ( templateParams.error && 'user' === initiatedBy ) {
				/*
				 * Errors messages are only shown for requests that were initiated
				 * by the user, not for ones that were initiated by the app itself.
				 * Showing error messages for an event that user isn't aware of
				 * could be confusing or unnecessarily distracting.
				 */
				wp.a11y.speak( nearbyWPData.l10n.error_occurred_please_try_again );

				elementVisibility['.nearbywp-errors']         = true;
				elementVisibility['.nearbywp-error-occurred'] = true;

			} else {
				$locationMessage.text( nearbyWPData.l10n.enter_closest_city );

				elementVisibility['#nearbywp-location-message'] = true;
				elementVisibility['#nearbywp-toggle-location']  = true;
			}

			// Set the visibility of toggleable elements.
			_.each( elementVisibility, function( isVisible, element ) {
				$( element ).attr( 'aria-hidden', ! isVisible );
			} );

			$( '#nearbywp-toggle-location' ).attr( 'aria-expanded', elementVisibility['#nearbywp-toggle-location'] );

			/*
			 * During the initial page load, the location form should be hidden
			 * by default if the user has saved a valid location during a previous
			 * session. It's safe to assume that they want to continue using that
			 * location, and displaying the form would unnecessarily clutter the
			 * widget.
			 */
			if ( 'app' === initiatedBy && templateParams.location.description ) {
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
