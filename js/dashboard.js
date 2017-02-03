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
					if ( window.navigator.geolocation ) {
						navigator.geolocation.getCurrentPosition(
							function( position ) {
								app.getEvents( {
									coordinates: position.coords
								} )
							},
							function() {
								alert( "Unable to retrieve your location" );
							}
						);
					}

					$( this ).replaceWith( $( '#nearbywp-form' ) ).show();
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

			wp.ajax.post( 'nearbywp_get_events', data )
				.done( function( events ) {
					var template = wp.template( 'nearbywp' );

					$( '#nearbywp' ).html( template( events ) );

				} )
				.fail( function( error ) {
					$( '#nearbywp' ).text( error.message );
				} );
		}
	};

	app.init();
} );
