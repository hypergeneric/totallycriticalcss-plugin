<?php

namespace Classes\Core;

class Critical {

	public function totallycriticalcss_save_post_action() {
		add_action( 'save_post', array( $this, 'totallycriticalcss_post_save' ) );
		add_action( 'add_meta_boxes', array( $this, 'totallycriticalcss_metabox' ) );
	}

	/**
	* Store Path
	*/
	public function totallycriticalcss_custom_theme_path() {
		$theme_path = get_option( 'totallycriticalcss_custom_theme_location' ) ? get_option( 'totallycriticalcss_custom_theme_location' ) : get_template_directory_uri();

		return $theme_path;
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
	public function totallycriticalcss( $id ) {
		$cri = 'https://api.totallycriticalcss.com/v1/?';
		$url = get_permalink( $id );
		$pth = $this->totallycriticalcss_custom_theme_path();
		$css = $this->totallycriticalcss_stylesheet_path();
		$key = get_option( 'totallycriticalcss_api_key' ) ? get_option( 'totallycriticalcss_api_key' ) : 'beadf54f56063cc0cce7ded292b8e099';

		$in = file_get_contents( $cri . 'u=' . $url . '&c=' . $css . '&p=' . $pth . '&k=' . $key, false );

		if( $in ) {
			if ( ! add_post_meta( $id, 'totallycriticalcss', $in, true ) ) {
				update_post_meta( $id, 'totallycriticalcss', $in );
				$this->totallycriticalcss_metabox_callback( $id );
			}
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
		add_meta_box( 'totallycriticalcss_metabox_id', __( 'TotallyCriticalCSS', 'cr_crit' ), array( $this, 'totallycriticalcss_metabox_callback' ), 'page', 'side', 'high' );
	}

	public function totallycriticalcss_metabox_callback( $meta_id  ) {
		$status = get_post_meta( $meta_id->ID, 'totallycriticalcss', true ) ? '<strong style="color: green; text-transform: uppercase;">Generated</strong>' : '<strong style="color: red; text-transform: uppeprcase;">Not Generated</strong>';
		$output = 'TotallyCriticalCSS is '. $status;
		echo $output;
	}

}
