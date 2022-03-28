<?php

namespace Classes\Core;

class Critical {

	public function totallycriticalcss_save_post_action() {
		add_action( 'save_post', array( $this, 'totallycriticalcss' ) );
		add_action( 'add_meta_boxes', array( $this, 'totallycriticalcss_metabox' ) );
	}

	/**
	* TotallyCriticalCSS Function
	*/
	public function get_all_stylesheets() {
		$totallycriticalcss_simplemode = get_option( 'totallycriticalcss_simplemode' );
		$css = [];
		if ( $totallycriticalcss_simplemode ) {
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
	* TotallyCriticalCSS Function
	*/
	public function get_api_key() {
		return get_option( 'totallycriticalcss_api_key' ) ? get_option( 'totallycriticalcss_api_key' ) : 'beadf54f56063cc0cce7ded292b8e099';
	}
	
	public function get_totallycriticalcss( $id ) {
		
		$totallycriticalcss_simplemode = get_option( 'totallycriticalcss_simplemode' );
		
		$styles = $this->get_all_stylesheets();
		$css = [];
		foreach ( $styles as $handle => $url ) {
			$css[] = $url;
		}
		$css = implode( '::::', $css );
		$css = str_replace( 'totallycritical.lndo.site', 'totallycriticalcss.com', $css );
		
		// generate the url
		$uri = 'http://api.totallycriticalcss.com/v1/';
		$query = [
			'u' => str_replace( 'totallycritical.lndo.site', 'totallycriticalcss.com', get_permalink( $id ) ),
			'c' => $css,
			'k' => $this->get_api_key(),
			't' => md5( uniqid( '', true ) ),
		];
		
		// pull the critical
		$response = wp_remote_get( $uri . "?" . http_build_query( $query ), [ 'timeout' => 30 ] );
		if ( ! is_wp_error( $response ) ) {
			update_post_meta( $id, 'totallycriticalcss', $response['body'] );
		} else {
			$data = '{"success":false,"message":"' . addslashes( $response->get_error_message() ) . '"}';
			update_post_meta( $id, 'totallycriticalcss', $data );
		}
		
		// if it's simple mode, save the global checksum at this point in time to check in the future
		if ( $totallycriticalcss_simplemode ) {
			update_post_meta( $id, 'totallycriticalcss_checksum', get_transient( 'totallycriticalcss-sheetlist-checksum' ) );
		}
		
		// clear out any invalidation flag
		update_post_meta( $id, 'totallycriticalcss_invalidate', false );
		
	}
	
	/**
	* On Post Save Function
	*/
	public function totallycriticalcss( $id ) {
		
		$totallycriticalcss_always_immediate = get_option( 'totallycriticalcss_always_immediate' );
		
		if ( $totallycriticalcss_always_immediate ) {
			// if we are doing immediate, just run it now
			$this->get_totallycriticalcss( $id );
		} else {
			// otherwise, just set the invalidation flag as true
			update_post_meta( $id, 'totallycriticalcss_invalidate', true );
		}
		
		$this->totallycriticalcss_metabox_callback( $id );
		
	}

	/**
	* Register metabox
	*/
	public function totallycriticalcss_metabox() {
		$totallycriticalcss_show_metaboxes = get_option( 'totallycriticalcss_show_metaboxes' );
		$totallycriticalcss_simplemode = get_option( 'totallycriticalcss_simplemode' );
		if ( $totallycriticalcss_show_metaboxes ) {
			$selected_cpt = $totallycriticalcss_simplemode ? [ 'page', 'post', 'product' ] : get_option( 'totallycriticalcss_selected_cpt' );
			foreach ( $selected_cpt as $post_type ) {
				add_meta_box( 'totallycriticalcss_metabox_id', __( 'TotallyCriticalCSS', 'cr_crit' ), array( $this, 'totallycriticalcss_metabox_callback' ), $post_type, 'side', 'high' );
			}
		}
	}

	/**
	* Display metabox
	*/
	public function totallycriticalcss_metabox_callback( $meta_id  ) {
		$totallycriticalcss_invalidate = get_post_meta( $meta_id->ID, 'totallycriticalcss_invalidate', true );
		if ( $totallycriticalcss_invalidate ) {
			$status = 'TotallyCriticalCSS is <strong style="color: green; text-transform: uppercase;">Pending</strong>' ;
		} else {
			$totallycriticalcss = get_post_meta( $meta_id->ID, 'totallycriticalcss', true );
			if ( ! $totallycriticalcss ) {
				$status = 'TotallyCriticalCSS is <strong style="color: red; text-transform: uppeprcase;">Not Generated</strong>';
			} else {
				$totallycriticalcss = json_decode( $totallycriticalcss );
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
