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
			var selectedStyles = [];
			$( '#admin-view-form .row input:checked' ).each( function() {
				selectedStyles.push( { name: $( this ).val(), url: $( this ).data( 'url' ) } );
			} );

			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				data:{
					action: 'totallycriticalcss_save_admin_page',
					api_key: apiKey,
					custom_theme: customTheme,
					custom_stylesheet: customStylesheet,
					custom_dequeue: customDequeue,
					selected_styles: selectedStyles
				},
				success: function( response ) {
					location.reload();
				}
			} );
		} );

		$( '#admin-view-form .toggle-all' ).click( function() {
			$( '#admin-view-form .group input' ).click().change();
		} );


		// Tabs
		var clickedTab      = $("#tccssWrapper .tabs > .active");
		var tabWrapper      = $("#tccssWrapper .tab__content");
		var activeTab       = tabWrapper.find(".active");
		var activeTabHeight = activeTab.outerHeight();
		
		activeTab.show();
		
		tabWrapper.height(activeTabHeight);
		
		$("#tccssWrapper .tabs > li").on("click", function() {
			
			$("#tccssWrapper .tabs > li").removeClass("active");
			
			$(this).addClass("active");
			
			clickedTab = $("#tccssWrapper .tabs .active");
			
			activeTab.fadeOut(150, function() {
				
				$("#tccssWrapper .tab__content > li").removeClass("active");
				
				var clickedTabIndex = clickedTab.index();

				$("#tccssWrapper .tab__content > li").eq(clickedTabIndex).addClass("active");
				
				activeTab = $("#tccssWrapper .tab__content > .active");
				
				activeTabHeight = activeTab.outerHeight();
				
				tabWrapper.stop().delay(30).animate({
					height: activeTabHeight
				}, 200, function() {
					
					// Fade in active tab
					activeTab.delay(30).fadeIn(150);
					
				});
			});
		});
		
	
	});
} ( jQuery, window, document ) );