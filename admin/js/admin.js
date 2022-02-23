( function ( $, window, document ) {
	'use strict';
	$( document ).ready( function () {
		var ajaxURL = totallycriticalcss_obj.ajax_url;

		$( '#admin-view-form' ).submit( function( e ) {
			e.preventDefault();

			var apiKey = $( this ).find( '#apiKey' ).val();

			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				data:{
					action: 'save_admin_page',
					api_key: apiKey
				},
				success: function( response ) {
					console.log( response );
				}
			} );
		} );
	});
} ( jQuery, window, document ) );
