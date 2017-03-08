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

			app.getEvents();

			$container.on( 'click', '#nearbywp-toggle', function() {
				$( '#nearbywp-form' ).removeClass( 'hide' );
				$( '#nearbywp-location' ).focus();
			});

			$container.on( 'click', '#nearbywp-cancel', function() {
				$( '#nearbywp-form' ).addClass( 'hide' );
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
			var $spinner = $( '#nearbywp-form .spinner' );

			data          = data || {};
			data._wpnonce = nearbyWP.nonce;
			data.timezone = window.Intl ? window.Intl.DateTimeFormat().resolvedOptions().timeZone : '';

			$spinner.addClass( 'is-active' );

			wp.ajax.post( 'nearbywp_get_events', data )
				.always( function() {
					$spinner.removeClass( 'is-active' );
				})
				.done( function( events ) {
					var template = wp.template( 'nearbywp' );

					$( '#nearbywp' ).html( template( events ) );
				})
				.fail( function( error ) {
					$( '#nearbywp' ).html( error.message );
				});
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
