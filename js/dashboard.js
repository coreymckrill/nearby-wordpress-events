wp.NearbyWP = wp.NearbyWP || {};

jQuery( function( $ ) {
	'use strict';

	var app = wp.NearbyWP.Dashboard = {

		initialized: false,

		/**
		 * Main entry point
		 */
		init : function() {
			if ( app.initialized ) {
				return;
			}

			app.getEvents();

			$( '#nearbywp' )
				.on( 'click', '#nearbywp-toggle, #nearbywp-description', function() {
					$( '#nearbywp-form' ).removeClass( 'hide' );
					$( '#nearbywp-location' ).focus();
				} )
				.on( 'click', '#nearbywp-cancel', function() {
					$( '#nearbywp-form' ).addClass( 'hide' );
				} )
				.on( 'submit', '#nearbywp-form', function( event ) {
					event.preventDefault();

					app.getEvents( {
						location: $( '#nearbywp-location' ).val()
					} )
				} );

			app.initialized = true;
		},

		/**
		 * Send Ajax request to fetch events for the widget
		 *
		 * @param data
		 */
		getEvents : function( data ) {
			data = data || {};
			data._wpnonce = nearbyWP.nonce;
			data.tz = window.Intl ? window.Intl.DateTimeFormat().resolvedOptions().timeZone : '';

			$( '#nearbywp-form .spinner' ).addClass( 'is-active' );

			wp.ajax.post( 'nearbywp_get_events', data )
				.always( function() {
					$( '#nearbywp-form .spinner' ).removeClass( 'is-active' );
				} )
				.done( function( events ) {
					var template = wp.template( 'nearbywp' );

					$( '#nearbywp' ).html( template( events ) );
				} )
				.fail( function( error ) {
					$( '#nearbywp' ).html( error.message );
				} );
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

	// todo Maybe incorporate the ajaxWidgets stuff related to `dashboard_primary`
	// found in wp-admin/js/dashboard.js
} );
