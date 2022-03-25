<?php

namespace Classes\Queue;

require_once __DIR__ . '/Core.php';

use Classes\Core\Critical;

class Queue {

	public function __construct() {
		//add_action( 'wp_print_styles', array( $this, 'totallycriticalcss_custom_style_dequeueing' ) );
		add_action( 'wp_print_styles', array( $this, 'totallycriticalcss_selected_style_dequeueing' ), 100 );
		add_action( 'wp_enqueue_scripts', array( $this, 'totallycriticalcss_style' ) );
	}

	/**
	* Dequeue specific custom styles
	*/
	public function totallycriticalcss_custom_style_dequeueing() {
		$user_dequeued_stylesheet = get_option( 'totallycriticalcss_custom_dequeue' );
		if( $user_dequeued_stylesheet ) {
			$explosion = explode( ',', $user_dequeued_stylesheet );
			foreach( $explosion as $style ) {
				wp_dequeue_style( $style );
				wp_deregister_style( $style );
			}
		}
	}

	/**
	* Dequeue/ enqueue selected styles
	*/
	public function totallycriticalcss_selected_style_dequeueing() {
		$selected_stylesheet_dequeue = get_option( 'totallycriticalcss_selected_styles' );
		if ( $selected_stylesheet_dequeue ) {
			foreach ( $selected_stylesheet_dequeue as $style ) {
				$name = $style[ 'name' ];
				wp_dequeue_style( $name );
			}
		}
		add_action( 'get_footer', function() {
			$selected_stylesheet_dequeue = get_option( 'totallycriticalcss_selected_styles' );
			if ( $selected_stylesheet_dequeue ) {
				foreach ( $selected_stylesheet_dequeue as $style ) {
					$name = $style[ 'name' ];
					$url  = $style[ 'url' ];
					wp_enqueue_style( $name, $url, false, null, 'all' );
				}
			}
		} );
	}

	/**
	* Enqueue TotallyCriticalCSS style
	*/
	public function totallycriticalcss_style() {
		$data = get_post_meta( $meta_id->ID, 'totallycriticalcss', true );
		if ( $data ) {
			$data = json_decode( $data );
			if ( $data !== null ) {
				echo '<!-- TotallyCriticalCSS --><style>' . $data->data . '</style><!-- /TotallyCriticalCSS -->';
			}
		}
	}

}
