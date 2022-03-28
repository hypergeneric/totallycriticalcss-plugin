<?php

namespace Classes\Queue;

require_once __DIR__ . '/Core.php';

use Classes\Core\Critical;

class Queue {

	public function __construct() {
		add_action( 'wp_print_styles', array( $this, 'totallycriticalcss_selected_style_dequeueing' ), 100 );
		add_action( 'wp_enqueue_scripts', array( $this, 'totallycriticalcss_style' ) );
	}

	/**
	* Dequeue/ enqueue selected styles
	*/
	public function totallycriticalcss_selected_style_dequeueing() {
		return;
		$selected_stylesheet_dequeue = get_option( 'totallycriticalcss_selected_styles' );
		if ( $selected_stylesheet_dequeue ) {
			foreach ( $selected_stylesheet_dequeue as $handle => $url ) {
				wp_dequeue_style( $handle );
			}
		}
		add_action( 'get_footer', function() {
			$selected_stylesheet_dequeue = get_option( 'totallycriticalcss_selected_styles' );
			if ( $selected_stylesheet_dequeue ) {
				foreach ( $selected_stylesheet_dequeue as $handle => $url ) {
					wp_enqueue_style( $handle, $url, false, null, 'all' );
				}
			}
		} );
	}

	/**
	* Enqueue TotallyCriticalCSS style
	*/
	public function totallycriticalcss_style() {
		$data = get_post_meta( get_the_ID(), 'totallycriticalcss', true );
		if ( $data ) {
			$data = json_decode( $data );
			if ( $data !== null ) {
				echo '<!-- TotallyCriticalCSS --><style>' . $data->data . '</style><!-- /TotallyCriticalCSS -->';
			}
		}
	}

}
