wp.NearbyWP = wp.NearbyWP || {};

jQuery( function( $ ) {
	'use strict';

	var app = wp.NearbyWP.Dashboard = {

		/**
		 * Main entry point
		 */
		init : function() {
			app.getEvents();

			$( '#nearbywp' )
				.on( 'click', '#nearbywp-toggle', function() {
					$( this ).hide();
					$( '#nearbywp-form' ).show();
					$('#nearbywp-location').focus();
				} )
				.on( 'click', '#nearbywp-cancel', function( event ) {
					$( '#nearbywp-form' ).hide();
					$( '#nearbywp-toggle' ).show();
				} )
				.on( 'submit', '#nearbywp-form', function( event ) {
					event.preventDefault();

					app.getEvents( {
						location: $( '#nearbywp-location' ).val()
					} )
				} );
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

	app.init();

	// todo Maybe incorporate the ajaxWidgets stuff related to `dashboard_primary`
	// found in wp-admin/js/dashboard.js
} );
