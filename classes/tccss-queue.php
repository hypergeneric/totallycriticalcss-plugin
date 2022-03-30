<?php

class TCCSS_Queue {

	public function __construct() {
		tccss()->log( 'Queue created' );
		add_action( 'wp_print_styles', array( $this, 'check_invalidate' ), 99998 );
		add_action( 'wp_print_styles', array( $this, 'dequeue_enqueue_handles' ), 99999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_critical' ) );
	}
	
	/**
	* Dequeue/ enqueue selected styles
	*/
	public function check_invalidate() {
		
		tccss()->processor()->validate();
		
	}

	/**
	* Dequeue/ enqueue selected styles
	*/
	public function dequeue_enqueue_handles() {
		
		// if the preview flag is enabled, ignore the switcheroo
		$preview = isset( $_GET['totallycriticalcss'] ) ? $_GET['totallycriticalcss'] : false;
		if ( $preview == 'preview' ) {
			return;
		}
		
		// only do this if it's ready
		if ( ! tccss()->processor()->processed() ) {
			return;
		}
		
		// dequeue stuff
		$styles = tccss()->sheetlist()->get_selected();
		foreach ( $styles as $handle => $url ) {
			wp_dequeue_style( $handle );
		}
		
		add_action( 'get_footer', function() {
			
			// enqueue stuff
			$styles = tccss()->sheetlist()->get_selected();
			foreach ( $styles as $handle => $url ) {
				wp_enqueue_style( $handle, $url, false, null, 'all' );
			}
			
		} );
		
	}

	/**
	* Enqueue TotallyCriticalCSS style
	*/
	public function enqueue_critical() {
		
		// only do this if it's ready
		if ( ! tccss()->processor()->processed() ) {
			return;
		}
		
		echo '<!-- TotallyCriticalCSS --><style>' . tccss()->processor()->get_data()->data->css . '</style><!-- /TotallyCriticalCSS -->' . "\n";
		
	}

}
