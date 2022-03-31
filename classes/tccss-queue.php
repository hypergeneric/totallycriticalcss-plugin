<?php

class TCCSS_Queue {

	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
		tccss()->log( 'Queue created' );
		add_action( 'wp_print_styles', array( $this, 'check_invalidate' ), 99998 );
		add_action( 'wp_print_styles', array( $this, 'dequeue_enqueue_handles' ), 99999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_critical' ) );
	}
	
	/**
	 * check_invalidate
	 *
	 * Check the page to see if it needs to be re-processed.
	 *
	 * @param   void
	 * @return  void
	 */
	public function check_invalidate() {
		
		tccss()->processor()->validate();
		
	}

	/**
	 * dequeue_enqueue_handles
	 *
	 * Dequeue/ enqueue selected styles
	 *
	 * @param   void
	 * @return  void
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
		$adminmode = tccss()->options()->get( 'adminmode' );
		$styles    = tccss()->sheetlist()->get_selected();
		foreach ( $styles as $handle => $url ) {
			if ( $adminmode == true ) {
				echo '<!-- TCSSS: dequeue: ( ' . $handle . ' ): ' . $url . ' -->' . "\n";
			} else {
				wp_dequeue_style( $handle );
			}
		}
		
		add_action( 'get_footer', function() {
			
			// enqueue stuff
			$adminmode = tccss()->options()->get( 'adminmode' );
			$styles    = tccss()->sheetlist()->get_selected();
			foreach ( $styles as $handle => $url ) {
				if ( $adminmode == true ) {
					echo '<!-- TCSSS: enqueue: ( ' . $handle . ' ): ' . $url . ' -->' . "\n";
				} else {
					wp_enqueue_style( $handle, $url, false, null, 'all' );
				}
			}
			
		} );
		
	}

	/**
	 * enqueue_critical
	 *
	 * Enqueue TotallyCriticalCSS style
	 *
	 * @param   void
	 * @return  void
	 */
	public function enqueue_critical() {
		
		// only do this if it's ready
		if ( ! tccss()->processor()->processed() ) {
			return;
		}
		
		$adminmode   = tccss()->options()->get( 'adminmode' );
		$criticalcss = tccss()->processor()->get_data();
		if ( $adminmode == true ) {
			echo '<!-- TCSSS: data: ' . print_r( $criticalcss, true ) . ' -->' . "\n";
		} else {
			echo '<!-- TCSSS --><style>' . $criticalcss->data->css . '</style><!-- /TCSSS -->' . "\n";
		}
		
	}

}
