<?php

class TCCSS_Queue {

	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
		if ( get_post_status() != 'publish' ) {
			tccss()->log( 'Published, live pages only.' );
			return;
		}
		if ( ! tccss()->options()->get( 'api_key' ) ) {
			tccss()->log( 'API key not supplied.' );
			return;
		}
		tccss()->log( 'Queue created: ' . tccss()->processor()->get_request_uri() );
		add_action( 'wp_print_styles', array( $this, 'handle_critical' ), apply_filters( 'tccss_action_priority', TCCSS_ACTION_PRIORITY ) );
	}

	/**
	 * handle_critical
	 *
	 * Dequeue/ enqueue selected styles
	 *
	 * @param   void
	 * @return  void
	 */
	public function handle_critical() {
		
		// if the preview flag is enabled, ignore the switcheroo
		$preview = isset( $_GET['totallycriticalcss'] ) ? $_GET['totallycriticalcss'] : false;
		if ( $preview == 'preview' ) {
			return;
		}
		
		// run the invalidation routine
		tccss()->processor()->validate();
		
		// only do this if it's ready
		if ( ! tccss()->processor()->processed() ) {
			return;
		}
		
		// dequeue stuff
		$adminmode   = tccss()->options()->get( 'adminmode' );
		$is_admin    = current_user_can( 'administrator' );
		$styles      = tccss()->sheetlist()->get_selected();
		$criticalcss = tccss()->processor()->get_data();
		foreach ( $styles as $handle => $url ) {
			if ( $adminmode == true && $is_admin ) {
				echo '<!-- TCSSS: dequeue: ( ' . $handle . ' ): ' . $url . ' -->' . "\n";
			} else {
				wp_dequeue_style( $handle );
			}
		}
		
		// dump critical into head
		if ( $adminmode == true && $is_admin ) {
			echo '<!-- TCSSS: data: ' . print_r( $criticalcss, true ) . ' -->' . "\n";
		} else {
			echo '<!-- TCSSS --><style>' . $criticalcss->data->css . '</style><!-- /TCSSS -->' . "\n";
		}
		
		// add it to the footer instead
		add_action( 'get_footer', function() {
			
			// enqueue stuff
			$adminmode = tccss()->options()->get( 'adminmode' );
			$is_admin  = current_user_can( 'administrator' );
			$styles    = tccss()->sheetlist()->get_selected();
			foreach ( $styles as $handle => $url ) {
				if ( $adminmode == true && $is_admin ) {
					echo '<!-- TCSSS: enqueue: ( ' . $handle . ' ): ' . $url . ' -->' . "\n";
				} else {
					echo '<link rel="preload" href="' . $url . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
					echo '<noscript><link rel="stylesheet" href="' . $url . '"></noscript>';
				}
			}
			
		} );
		
	}

}
