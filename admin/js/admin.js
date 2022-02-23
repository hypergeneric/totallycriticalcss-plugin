( function ( $, window, document ) {
	'use strict';
	$( document ).ready( function () {
		var ajaxURL = totallycriticalcss_obj.ajax_url;

		$( '#admin-view-form' ).submit( function( e ) {
			e.preventDefault();

			var apiKey = $( this ).find( '#apiKey' ).val();
			var customStylesheet = $( this ).find( '#customStylesheet' ).val();
			var customTheme = $( this ).find( '#customTheme' ).val();
			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				data:{
					action: 'save_admin_page',
					api_key: apiKey,
					custom_stylesheet: customStylesheet,
					custom_theme: customTheme
				},
				success: function( response ) {
					console.log( response)
					location.reload();
				}
			} );
		} );
	});
} ( jQuery, window, document ) );//https://tealium.lndo.site/wp-content/themes/Jupiter-child/jasper/assets/css/style.min.css
