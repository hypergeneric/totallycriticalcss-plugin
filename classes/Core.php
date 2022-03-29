<?php

namespace Classes\Core;

class Critical {
	
	/**
	* Add Save / Post hooks
	*/
	public function init_hooks() {
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}
	
	/**
	* On Post Save Function
	*/
	public function save_post( $id, $post ) {
		
		// do not save if this is an auto save routine
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $id;
		}
		
		// only save once! WordPress save's a revision as well.
		if ( wp_is_post_revision( $id ) ) {
			return $id;
		}
		
		// only process critical css for published pages
		if ( $post->post_status != "publish" ) {
			return $id;
		}
		
		write_log( 'save_post: ' . $id );
		
		$totallycriticalcss_always_immediate = get_option( 'totallycriticalcss_always_immediate' );
		
		if ( $totallycriticalcss_always_immediate ) {
			// if we are doing immediate, just run it now
			$this->get_totallycriticalcss( $id );
		} else {
			// otherwise, just set the invalidation flag as true
			update_post_meta( $id, 'totallycriticalcss_invalidate', true );
		}
		
	}
	
	public function get_totallycriticalcssarchive( $route ) {
		
		write_log( 'get_totallycriticalcss: ' . $route );
		
		$totallycriticalcss_simplemode = get_option( 'totallycriticalcss_simplemode' );
		$custom_routes = get_option( 'totallycriticalcss_custom_routes' );
		
		// should we process this route?
		$process_route = $totallycriticalcss_simplemode;
		if ( $process_route == false && $custom_routes ) {
			foreach ( $custom_routes as $custom_route ) {
				if ( strtolower( trim( $custom_route, '/' ) ) == strtolower( trim( $route, '/' ) ) ) {
					$process_route = true;
				}
			}
		}
		if ( $process_route == false ) {
			return;
		}
		
		$invalidate = \TotallyCriticalCSS::get_invalidate();
		if ( $invalidate == 'loading' ) {
			return;
		}
		
		// clear out any invalidation flag
		write_log( 'get_totallycriticalcss: ' . $route . ' loading' );
		\TotallyCriticalCSS::set_invalidate( 'loading' );
		
		// get all the styles and concatenate
		$stylesheets = $this->get_all_stylesheets();
		$css = [];
		foreach ( $stylesheets as $handle => $url ) {
			$css[] = $url;
		}
		$css = implode( '::::', $css );
		$css = str_replace( 'totallycritical.lndo.site', 'totallycriticalcss.com', $css );
		
		// get the post url
		$url = home_url( $route );
		$url = str_replace( 'totallycritical.lndo.site', 'totallycriticalcss.com', $url );
		
		// generate the url
		$uri = 'http://api.totallycriticalcss.com/v1/';
		$query = [
			'u' => $url,
			'c' => $css,
			'k' => get_option( 'totallycriticalcss_api_key' ),
			't' => md5( uniqid( '', true ) ),
		];

		// pull the critical and save it
		$response      = wp_remote_get( $uri . "?" . http_build_query( $query ), [ 'timeout' => 30 ] );
		$response_data = ! is_wp_error( $response ) ? $response['body'] : '{"success":false,"message":"' . addslashes( $response->get_error_message() ) . '"}';
		\TotallyCriticalCSS::set_data( json_decode( $response_data ) );
		
		// if it's simple mode, save the global checksum at this point in time to check in the future
		if ( $totallycriticalcss_simplemode ) {
			\TotallyCriticalCSS::set_checksum( get_transient( 'totallycriticalcss-sheetlist-checksum' ) );
		}
		
		// clear out any invalidation flag
		write_log( 'get_totallycriticalcss: ' . $route . ' loaded!' );
		\TotallyCriticalCSS::set_invalidate( false );
		
	}
	
	public function get_totallycriticalcss( $id ) {
		
		write_log( 'get_totallycriticalcss: ' . $id );
		
		$totallycriticalcss_simplemode = get_option( 'totallycriticalcss_simplemode' );
		$totallycriticalcss_invalidate = get_post_meta( $id, 'totallycriticalcss_invalidate', true );
		
		if ( $totallycriticalcss_invalidate == 'loading' ) {
			return;
		}
		
		// clear out any invalidation flag
		write_log( 'get_totallycriticalcss: ' . $id . ' loading' );
		update_post_meta( $id, 'totallycriticalcss_invalidate', 'loading' );
		
		// get all the styles and concatenate
		$stylesheets = $this->get_all_stylesheets();
		$css = [];
		foreach ( $stylesheets as $handle => $url ) {
			$css[] = $url;
		}
		$css = implode( '::::', $css );
		$css = str_replace( 'totallycritical.lndo.site', 'totallycriticalcss.com', $css );
		
		// get the post url
		$url = get_permalink( $id );
		$url = str_replace( 'totallycritical.lndo.site', 'totallycriticalcss.com', $url );
		
		// generate the url
		$uri = 'http://api.totallycriticalcss.com/v1/';
		$query = [
			'u' => $url,
			'c' => $css,
			'k' => get_option( 'totallycriticalcss_api_key' ),
			't' => md5( uniqid( '', true ) ),
		];

		// pull the critical and save it
		$response      = wp_remote_get( $uri . "?" . http_build_query( $query ), [ 'timeout' => 30 ] );
		$response_data = ! is_wp_error( $response ) ? $response['body'] : '{"success":false,"message":"' . addslashes( $response->get_error_message() ) . '"}';
		update_post_meta( $id, 'totallycriticalcss', json_decode( $response_data ) );
		
		// if it's simple mode, save the global checksum at this point in time to check in the future
		if ( $totallycriticalcss_simplemode ) {
			update_post_meta( $id, 'totallycriticalcss_checksum', get_transient( 'totallycriticalcss-sheetlist-checksum' ) );
		}
		
		// clear out any invalidation flag
		write_log( 'get_totallycriticalcss: ' . $id . ' loaded!' );
		update_post_meta( $id, 'totallycriticalcss_invalidate', false );
		
	}

	/**
	* TotallyCriticalCSS Function
	*/
	public function get_all_stylesheets() {
		
		$totallycriticalcss_simplemode = get_option( 'totallycriticalcss_simplemode' );
		$css = [];
		
		if ( $totallycriticalcss_simplemode ) {
			
			// if we are using simplemode, get all the current handles, and save them to a transient
			// also, save a hash of the data plus a timestamp to check against for future calls
			// we are going to do this no more than once a day
			$sheetlist = get_transient( 'totallycriticalcss-sheetlist' );
			if ( ! $sheetlist ) {
				$sheetlist = \TotallyCriticalCSS::get_current_sheetlist();
				set_transient( 'totallycriticalcss-sheetlist', $sheetlist, 86400 );
				set_transient( 'totallycriticalcss-sheetlist-checksum', md5( serialize ( $sheetlist ) ), 86400 );
			}
			foreach ( $sheetlist as $handle => $url ) {
				$css = $sheetlist;
			}
			
		} else {
			
			$totallycriticalcss_custom_dequeue = get_option( 'totallycriticalcss_custom_dequeue' );
			if ( $totallycriticalcss_custom_dequeue ) {
				foreach ( $totallycriticalcss_custom_dequeue as $handle => $url ) {
					$css[$handle] = $url;
				}
			}
			$totallycriticalcss_selected_styles = get_option( 'totallycriticalcss_selected_styles' );
			if ( $totallycriticalcss_selected_styles ) {
				foreach ( $totallycriticalcss_selected_styles as $handle => $url ) {
					$css[$handle] = $url;
				}
			}
			
		}
		return $css;
	}
	
	/**
	* Register metabox
	*/
	public function add_meta_boxes() {
		$totallycriticalcss_show_metaboxes = get_option( 'totallycriticalcss_show_metaboxes' );
		if ( $totallycriticalcss_show_metaboxes ) {
			$totallycriticalcss_simplemode = get_option( 'totallycriticalcss_simplemode' );
			$selected_cpt = $totallycriticalcss_simplemode ? [ 'page', 'post', 'product' ] : get_option( 'totallycriticalcss_selected_cpt' );
			foreach ( $selected_cpt as $post_type ) {
				add_meta_box( 'totallycriticalcss_metabox_id', __( 'TotallyCriticalCSS', 'cr_crit' ), array( $this, 'metabox_callback' ), $post_type, 'side', 'high' );
			}
		}
	}

	/**
	* Display metabox
	*/
	public function metabox_callback( $post ) {
		$totallycriticalcss_invalidate = get_post_meta( $post->ID, 'totallycriticalcss_invalidate', true );
		if ( $totallycriticalcss_invalidate ) {
			$status = 'TotallyCriticalCSS is <strong style="color: green; text-transform: uppercase;">Pending</strong>' ;
		} else {
			$totallycriticalcss = get_post_meta( $post->ID, 'totallycriticalcss', true );
			if ( ! $totallycriticalcss ) {
				$status = 'TotallyCriticalCSS is <strong style="color: red; text-transform: uppeprcase;">Not Generated</strong>';
			} else {
				if ( $totallycriticalcss == null ) {
					$status = '<strong style="color: red; text-transform: uppeprcase;">Error: Invalid Server Response</strong>';
				} else  {
					$status = $totallycriticalcss->success === true ? 'TotallyCriticalCSS is <strong style="color: green; text-transform: uppercase;">Generated</strong>' : '<strong style="color: red; text-transform: uppeprcase;">Error: ' . $totallycriticalcss->message . '</strong>';
				}
			}
		}
		echo $status;
	}

}
