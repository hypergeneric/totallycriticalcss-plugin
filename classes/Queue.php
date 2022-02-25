<?php

namespace Classes\Queue;

require_once __DIR__ . '/Core.php';

use Classes\Core\Critical;

$critical = new Critical();

class Queue {

	public function __construct() {
		add_action( 'wp_print_styles', array( $this, 'totallycriticalcss_custom_style_dequeueing' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'totallycriticalcss_main_style_dequeueing' ) );
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
	* Dequeue/ enqueue main theme style
	*/
	public function totallycriticalcss_main_style_dequeueing() {

		$stylesheet_name = get_stylesheet();
		$stylesheet_uri = get_stylesheet_uri();
		wp_dequeue_style( $stylesheet_name );

		add_action( 'get_footer', function() {
			$stylesheet_name = get_stylesheet();
			$stylesheet_uri = get_stylesheet_uri();

			wp_enqueue_style( $stylesheet_name, $stylesheet_uri, false, null, 'all' );
		} );

	}

	/**
	* Enqueue TotallyCriticalCSS style
	*/
	public function totallycriticalcss_style() {

		$totallyCiriticalCSS = get_post_meta( get_the_ID(), 'totallycriticalcss', true );
		$style_name = get_post_field( 'post_name', get_the_ID() );

		if( $totallyCiriticalCSS ) {
			echo '<!-- TotallyCriticalCSS --><style>' . $totallyCiriticalCSS . '</style><!-- /TotallyCriticalCSS -->';
			add_action( 'get_footer', function() {
				wp_enqueue_style( $style_name . '-style', $critical->totallycriticalcss_stylesheet_path(), false, null, 'all' );
			});
		} else {
			wp_enqueue_style( $style_name . '-style', $critical->totallycriticalcss_stylesheet_path(), false, null, 'all' );
		}

	}

}
