( function ( $, window, document ) {
	'use strict';
	$( document ).ready( function () {
		
		var ajaxURL = totallycriticalcss_obj.ajax_url;
		var tccssadmin = $( '#tccssWrapper' );
		
		if ( tccssadmin.length == 0 ) {
			return;
		}
		
		// common to all
		
		tccssadmin.find( '.adder-form-show' ).click( function( e ) {
			$( this ).prev().show();
			$( this ).hide();
			e.preventDefault();
			return false;
		} );
		
		tccssadmin.find( '.adder-form-cancel' ).click( function( e ) {
			$( this ).parent().next().show();
			$( this ).parent().hide();
			e.preventDefault();
			return false;
		} );
		
		// custom dequeue functions / actions
		
		function createCustomDequeueTable ( data ) {
			tccssadmin.find( '#custom_dequeue tbody tr:not( .seed )' ).remove();
			var seed = tccssadmin.find( '#custom_dequeue tbody tr.seed' );
			for ( var handle in data ) {
				if ( data.hasOwnProperty( handle ) ) {
					var url = data[ handle ];
					var clone = seed.clone( true );
					clone.removeClass( 'seed' );
					clone.find( '.handle' ).text( '( ' + handle + ' )' );
					clone.find( '.url' ).text(url );
					clone.find( '.button-delete' ).attr( 'data-handle', handle );
					clone.find( '.button-delete' ).data( 'handle', handle );
					tccssadmin.find( '#custom_dequeue tbody' ).append( clone );
				}
			}
		}
		
		tccssadmin.find( '#add-form-custum-dequeue' ).click( function( e ) {
			$( '#custom_dequeue' ).addClass( 'loading' );
			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				data:{
					action: 'totallycriticalcss_add_custum_dequeue',
					form_handle: $( '#add-form-handle' ).val(),
					form_url: $( '#add-form-url' ).val(),
				},
				success: function( response ) {
					$( '#custom_dequeue' ).removeClass( 'loading' );
					tccssadmin.find( '.adder-form input' ).val( '' );
					tccssadmin.find( '.adder-form-cancel' ).click();
					createCustomDequeueTable( response.data );
				}
			} );
			e.preventDefault();
			return false;
		} );
		
		tccssadmin.find( '.custum-dequeue-delete' ).click( function( e ) {
			$( '#custom_dequeue' ).addClass( 'loading' );
			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				data:{
					action: 'totallycriticalcss_delete_custum_dequeue',
					form_handle: $( this ).data( 'handle' ),
				},
				success: function( response ) {
					$( '#custom_dequeue' ).removeClass( 'loading' );
					createCustomDequeueTable( response.data );
				}
			} );
			e.preventDefault();
			return false;
		} );
		
		// custom routes functions / actions
		
		function createCustomRouteTable ( data ) {
			tccssadmin.find( '#custom_routes tbody tr:not( .seed )' ).remove();
			var seed = tccssadmin.find( '#custom_routes tbody tr.seed' );
			for ( var i = 0; i < data.length; i++ ) {
				var url = data[ i ];
				var clone = seed.clone( true );
				clone.removeClass( 'seed' );
				clone.find( '.route' ).text( url );
				clone.find( '.button-delete' ).attr( 'data-url', url );
				clone.find( '.button-delete' ).data( 'url', url );
				tccssadmin.find( '#custom_routes tbody' ).append( clone );
			}
		}
		
		tccssadmin.find( '#add-form-custum-route' ).click( function( e ) {
			$( '#custom_routes' ).addClass( 'loading' );
			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				data:{
					action: 'totallycriticalcss_add_custum_route',
					form_url: $( '#add-route-url' ).val(),
				},
				success: function( response ) {
					$( '#custom_routes' ).removeClass( 'loading' );
					tccssadmin.find( '.adder-form-cancel' ).click();
					tccssadmin.find( '.adder-form input' ).val( '' );
					createCustomRouteTable( response.data );
					console.log(response);
				}
			} );
			e.preventDefault();
			return false;
		} );
		
		tccssadmin.find( '.custom-route-delete' ).click( function( e ) {
			$( '#custom_routes' ).addClass( 'loading' );
			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				data:{
					action: 'totallycriticalcss_delete_custum_route',
					form_url: $( this ).data( 'url' ),
				},
				success: function( response ) {
					$( '#custom_routes' ).removeClass( 'loading' );
					createCustomRouteTable( response.data );
					console.log(response);
				}
			} );
			e.preventDefault();
			return false;
		} );
		
		// everything else
		
		$( '#admin-view-form' ).submit( function( e ) {
			
			e.preventDefault();

			var api_key          = $( this ).find( '#api_key' ).val();
			var simplemode       = $( this ).find( '#simplemode' ).is( ':checked' );
			var show_metaboxes   = $( this ).find( '#show_metaboxes' ).is( ':checked' );
			var always_immediate = $( this ).find( '#always_immediate' ).is( ':checked' );
			var adminmode        = $( this ).find( '#adminmode' ).is( ':checked' );
			
			var selected_styles = {};
			$( '#admin-view-form input[name="sheets"]:checked' ).each( function() {
				var handle = $( this ).val();
				var url    = $( this ).data( 'url' );
				selected_styles[handle] = url;
			} );
			
			var selected_cpt = [];
			$( '#admin-view-form input[name="selected_cpt"]:checked' ).each( function() {
				selected_cpt.push( $( this ).val() );
			} );

			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				data:{
					action: 'totallycriticalcss_save_admin_page',
					api_key: api_key,
					simplemode: simplemode,
					show_metaboxes: show_metaboxes,
					always_immediate: always_immediate,
					adminmode: adminmode,
					selected_styles: selected_styles,
					selected_cpt: selected_cpt
				},
				success: function( response ) {
					location.reload();
				}
			} );
			
		} );
		
		$( '#styles-toggle-all' ).click( function( e ) {
			$( '#admin-view-form input[name="sheets"]' ).each( function () { this.checked = !this.checked; } );
			e.preventDefault();
			return false;
		} );
		
		// tabs
		var tabs         = tccssadmin.find( '.tabs > li' );
		var tabs_content = tccssadmin.find( '.tab__content > li' );
		var page_hash    = window.location.hash == '' ? tabs.first().data( 'tab' ) : window.location.hash.substr( 1 );
		
		function setCurrentTab ( hash ) {
			tabs.each( function () {
				if ( $( this ).data( 'tab' ) == hash ) {
					tabs.removeClass( 'active' );
					$( this ).addClass( 'active' );
					tabs_content.removeClass( 'active' );
					$( '#tab-' + hash ).addClass( 'active' );
				}
			} );
			window.location.hash = hash;
		}
		
		tabs.click( function( e ) {
			if ( $( this ).hasClass( 'disabled' ) ) {
				return;
			}
			setCurrentTab( $( this ).data( 'tab' ) );
		} );
		
		setCurrentTab( page_hash );
	
	});
} ( jQuery, window, document ) );