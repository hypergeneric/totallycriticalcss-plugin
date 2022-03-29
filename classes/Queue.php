<?php

namespace Classes\Queue;

require_once __DIR__ . '/Core.php';

use Classes\Core\Critical;

class Queue {

	public function __construct() {
		write_log( 'Queue created' );
		add_action( 'wp_print_styles', array( $this, 'check_invalidate' ), 99998 );
		add_action( 'wp_print_styles', array( $this, 'dequeue_enqueue_handles' ), 99999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_critical' ) );
	}
	
	/**
	* Dequeue/ enqueue selected styles
	*/
	public function check_invalidate() {
		
		global $wp;
		write_log( 'totallycriticalcss_check_invalidate: ' . ( is_archive() ? $wp->request : get_the_ID() ) );
		
		$totallycriticalcss_simplemode = get_option( 'totallycriticalcss_simplemode' );
		
		$invalidate = \TotallyCriticalCSS::get_invalidate();
		$checksum = \TotallyCriticalCSS::get_checksum();
		
		// if it's simple mode, always check to see if the checksum is different that the current one
		// if so, run it again
		if ( $totallycriticalcss_simplemode ) {
			if ( $checksum != get_transient( 'totallycriticalcss-sheetlist-checksum' ) ) {
				$invalidate = true;
			}
		}
		
		// get the critical css
		if ( $invalidate ) {
			$critical = new Critical();
			if ( is_archive() ) {
				$critical->get_totallycriticalcssarchive( $wp->request );
			} else {
				$critical->get_totallycriticalcss( get_the_ID() );
			}
		}
		
	}

	/**
	* Dequeue/ enqueue selected styles
	*/
	public function dequeue_enqueue_handles() {
		
		// if the preview flag is enabled, ignore the switcheroo
		$totallycriticalcss_preview = isset( $_GET['totallycriticalcss'] ) ? $_GET['totallycriticalcss'] : false;
		if ( $totallycriticalcss_preview == 'preview' ) {
			return;
		}
		
		// only do the dequeueing / enqueuing if the data is live
		$data = \TotallyCriticalCSS::get_data();
		$invalidate = \TotallyCriticalCSS::get_invalidate();
		
		if ( ! $data || $data->success !== true || $invalidate == 'loading' ) {
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
	public function enqueue_critical() {
		
		$data = \TotallyCriticalCSS::get_data();
		$invalidate = \TotallyCriticalCSS::get_invalidate();
		
		if ( ! $data || $data->success !== true || $invalidate == 'loading' ) {
			return;
		}
		
		echo '<!-- TotallyCriticalCSS --><style>' . $data->data->css . '</style><!-- /TotallyCriticalCSS -->' . "\n";
		
	}

}
