<?php

namespace Classes\Core;

class Critical {

	public function totallycriticalcss_save_post_action() {
		add_action( 'save_post', array( $this, 'totallycriticalcss_post_save' ) );
		add_action( 'add_meta_boxes', array( $this, 'totallycriticalcss_metabox' ) );
	}

	/**
	* Store Stylesheet
	*/
	public function totallycriticalcss_stylesheet_path() {
		$stylesheet_path = get_option( 'totallycriticalcss_custom_stylesheet_location' ) ? get_option( 'totallycriticalcss_custom_stylesheet_location' ) : get_template_directory_uri() . '/style.css';

		return $stylesheet_path;
	}

	/**
	* TotallyCriticalCSS Function
	*/
	public function get_all_stylesheets() {
		$totallycriticalcss_selected_styles = get_option( 'totallycriticalcss_selected_styles' );
		if( $totallycriticalcss_selected_styles ) {
			$css = [];
			foreach ( $totallycriticalcss_selected_styles as $style) {
				$css[] = $style[ 'url' ];
			}
			$css = implode( '::::', $css );
		} else {
			$css = $this->totallycriticalcss_stylesheet_path();
		}
		$css = str_replace( 'totallycritical.lndo.site', 'totallycriticalcss.com', $css );
		return $css;
	}
	
	/**
	* TotallyCriticalCSS Function
	*/
	public function get_api_key() {
		return get_option( 'totallycriticalcss_api_key' ) ? get_option( 'totallycriticalcss_api_key' ) : 'beadf54f56063cc0cce7ded292b8e099';
	}
	
	public function totallycriticalcss( $id ) {
		
		$uri = 'http://api.totallycriticalcss.com/v1/';
		$query = [
			'u' => str_replace( 'totallycritical.lndo.site', 'totallycriticalcss.com', get_permalink( $id ) ),
			'c' => $this->get_all_stylesheets(),
			'k' => $this->get_api_key(),
			't' => md5( uniqid( '', true ) ),
		];
		
		$response = wp_remote_get( $uri . "?" . http_build_query( $query ), [ 'timeout' => 30 ] );
		if ( ! is_wp_error( $response ) ) {
			update_post_meta( $id, 'totallycriticalcss', $response['body'] );
			$this->totallycriticalcss_metabox_callback( $id );
		} else {
			$data = '{"success":false,"message":"' . addslashes( $response->get_error_message() ) . '"}';
			update_post_meta( $id, 'totallycriticalcss', $data );
			$this->totallycriticalcss_metabox_callback( $id );
		}
		
	}

	/**
	* On Post Save Function
	*/
	public function totallycriticalcss_post_save( $post_id ) {
		$this->totallycriticalcss( $post_id );
	}

	/**
	* Register metabox
	*/
	public function totallycriticalcss_metabox() {
		$my_post_types = get_option( 'totallycriticalcss_selected_cpt' );
		foreach ( $my_post_types as $my_post_type ) {
			add_meta_box( 'totallycriticalcss_metabox_id', __( 'TotallyCriticalCSS', 'cr_crit' ), array( $this, 'totallycriticalcss_metabox_callback' ), $my_post_type, 'side', 'high' );
		}
	}

	public function totallycriticalcss_metabox_callback( $meta_id  ) {
		$data = get_post_meta( $meta_id->ID, 'totallycriticalcss', true );
		if ( ! $data ) {
			$status = 'TotallyCriticalCSS is <strong style="color: red; text-transform: uppeprcase;">Not Generated</strong>';
		} else {
			$data = json_decode( $data );
			if ( $data == null ) {
				$status = '<strong style="color: red; text-transform: uppeprcase;">Error: Invalid Server Response</strong>';
			} else  {
				$status = $data->success === true ? 'TotallyCriticalCSS is <strong style="color: green; text-transform: uppercase;">Generated</strong>' : '<strong style="color: red; text-transform: uppeprcase;">Error: ' . $data->message . '</strong>';
			}
		}
		echo $status;
	}

}
