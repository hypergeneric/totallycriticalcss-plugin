<?php

namespace Classes\Queue;

require_once __DIR__ . '/Core.php';

use Classes\Core\Critical;

class Queue {

	public function __construct() {
		add_action( 'wp', array( $this, 'totallycriticalcss_check_invalidate' ) );
		add_action( 'wp_print_styles', array( $this, 'totallycriticalcss_selected_style_dequeueing' ), 100 );
		add_action( 'wp_enqueue_scripts', array( $this, 'totallycriticalcss_style' ) );
	}
	
	/**
	* Dequeue/ enqueue selected styles
	*/
	public function totallycriticalcss_check_invalidate() {
		
		$totallycriticalcss_simplemode = get_option( 'totallycriticalcss_simplemode' );
		$totallycriticalcss_invalidate = get_post_meta( get_the_ID(), 'totallycriticalcss_invalidate', true );
		$totallycriticalcss_checksum = get_post_meta( get_the_ID(), 'totallycriticalcss_checksum', true );
		
		// if it's simple mode, always check to see if the checksum is different that the current one
		// if so, run it again
		if ( $totallycriticalcss_simplemode ) {
			if ( $totallycriticalcss_checksum != get_transient( 'totallycriticalcss-sheetlist-checksum' ) ) {
				$totallycriticalcss_invalidate = true;
			}
		}
		
		// get the critical css
		if ( $totallycriticalcss_invalidate ) {
			$critical = new Critical();
			$critical->get_totallycriticalcss( get_the_ID() );
		}
		
	}

	/**
	* Dequeue/ enqueue selected styles
	*/
	public function totallycriticalcss_selected_style_dequeueing() {
		
		// if the preview flag is enabled, ignore the switcheroo
		$totallycriticalcss_preview = isset( $_GET['totallycriticalcss'] ) ? $_GET['totallycriticalcss'] : false;
		if ( $totallycriticalcss_preview == 'preview' ) {
			return;
		}
		
		// only do the dequeueing / enqueuing if the data is live
		$totallycriticalcss = get_post_meta( get_the_ID(), 'totallycriticalcss', true );
		$totallycriticalcss = json_decode( $totallycriticalcss );
		if ( ! $totallycriticalcss || $totallycriticalcss->success !== true ) {
			return;
		}
		
		// dequeue stuff
		$critical = new Critical();
		$styles = $critical->get_all_stylesheets();
		foreach ( $styles as $handle => $url ) {
			wp_dequeue_style( $handle );
		}
		
		add_action( 'get_footer', function() {
			
			// enqueue stuff
			$critical = new Critical();
			$styles = $critical->get_all_stylesheets();
			foreach ( $styles as $handle => $url ) {
				wp_enqueue_style( $handle, $url, false, null, 'all' );
			}
			
		} );
		
	}

	/**
	* Enqueue TotallyCriticalCSS style
	*/
	public function totallycriticalcss_style() {
		$totallycriticalcss = get_post_meta( get_the_ID(), 'totallycriticalcss', true );
		if ( $totallycriticalcss ) {
			$totallycriticalcss = json_decode( $totallycriticalcss );
			if ( $totallycriticalcss !== null && $totallycriticalcss->success === true ) {
				echo '<!-- TotallyCriticalCSS --><style>' . $totallycriticalcss->data . '</style><!-- /TotallyCriticalCSS -->';
			}
		}
	}

}
