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
				} )
				.on( 'click', '#nearbywp-cancel', function() {
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

		getEvents : function( data ) {
			data = data || {};
			data._wpnonce = nearbyWP.nonce;
			data.tz = new Date().toString().match(/\(([A-Za-z\s].*)\)/)[1];

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
} );
