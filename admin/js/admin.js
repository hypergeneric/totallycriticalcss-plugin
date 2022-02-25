( function ( $, window, document ) {
	'use strict';
	$( document ).ready( function () {
		var ajaxURL = totallycriticalcss_obj.ajax_url;

		$( '#admin-view-form' ).submit( function( e ) {
			e.preventDefault();

			var apiKey = $( this ).find( '#apiKey' ).val();
			var customTheme = $( this ).find( '#customTheme' ).val();
			var customStylesheet = $( this ).find( '#customStylesheet' ).val();
			var customDequeue = $( this ).find( '#customDequeue' ).val();

			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				data:{
					action: 'totallycriticalcss_save_admin_page',
					api_key: apiKey,
					custom_theme: customTheme,
					custom_stylesheet: customStylesheet,
					custom_dequeue: customDequeue
				},
				success: function( response ) {
					location.reload();
				}
			} );
		} );

		$( '#admin-view-form .toggle-all' ).click( function() {
			$( '#admin-view-form .group input' ).click()
		} );
	});
} ( jQuery, window, document ) );//https://tealium.lndo.site/wp-content/themes/Jupiter-child/jasper/assets/css/style.min.css
