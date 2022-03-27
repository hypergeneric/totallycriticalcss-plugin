( function ( $, window, document ) {
	'use strict';
	$( document ).ready( function () {
		var ajaxURL = totallycriticalcss_obj.ajax_url;
		
		$( '#show-custum-dequeue' ).click( function( e ) {
			$( '#custum-dequeue-add-form' ).show();
			$( this ).hide();
			e.preventDefault();
			return false;
		} );
		
		$( '#cancel-custum-dequeue' ).click( function( e ) {
			$( '#show-custum-dequeue' ).show();
			$( '#custum-dequeue-add-form' ).hide();
			e.preventDefault();
			return false;
		} );
		
		$( '#styles-toggle-all' ).click( function( e ) {
			$( '#admin-view-form input[name="sheets"]' ).each( function () { this.checked = !this.checked; } );
			e.preventDefault();
			return false;
		} );
		
		$( '#add-custum-dequeue' ).click( function( e ) {
			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				data:{
					action: 'totallycriticalcss_add_custum_dequeue',
					form_handle: $( '#add-form-handle' ).val(),
					form_url: $( '#add-form-url' ).val(),
				},
				success: function( response ) {
					console.log(response);
				}
			} );
			e.preventDefault();
			return false;
		} );
		
		$( '.dequeue-delete' ).click( function( e ) {
			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				data:{
					action: 'totallycriticalcss_delete_custum_dequeue',
					form_handle: $( this ).data( 'handle' ),
				},
				success: function( response ) {
					console.log(response);
				}
			} );
			e.preventDefault();
			return false;
		} );

		$( '#admin-view-form' ).submit( function( e ) {
			e.preventDefault();

			var apiKey = $( this ).find( '#apiKey' ).val();
			var customTheme = $( this ).find( '#customTheme' ).val();
			var customStylesheet = $( this ).find( '#customStylesheet' ).val();
			var selectedStyles = [];
			$( '#admin-view-form input[name="sheets"]:checked' ).each( function() {
				selectedStyles.push( { name: $( this ).val(), url: $( this ).data( 'url' ) } );
			} );
			var my_post_types = [];
			$( '#admin-view-form input[name="my_post_types"]:checked' ).each( function() {
				my_post_types.push( $( this ).val() );
			} );
			console.log(my_post_types);

			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				data:{
					action: 'totallycriticalcss_save_admin_page',
					api_key: apiKey,
					custom_theme: customTheme,
					custom_stylesheet: customStylesheet,
					selected_styles: selectedStyles,
					my_post_types: my_post_types
				},
				success: function( response ) {
					location.reload();
				}
			} );
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