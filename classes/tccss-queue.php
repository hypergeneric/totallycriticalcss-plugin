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
		$preview = filter_input( INPUT_GET, 'totallycriticalcss', FILTER_SANITIZE_STRING );
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
		$logged_in   = is_user_logged_in();
		$styles      = tccss()->sheetlist()->get_selected();
		$criticalcss = tccss()->processor()->get_data();
		foreach ( $styles as $handle => $url ) {
			if ( $adminmode == true && ! $logged_in ) {
				?><!-- TCSSS: dequeue: ( <?php echo esc_html( $handle ); ?> ): <?php echo esc_url( $url ); ?> -->
<?php
			} else {
				wp_dequeue_style( $handle );
			}
		}
		
		// dump critical into head
		if ( $adminmode == true && ! $logged_in ) {
			// do nothing
		} else {
			wp_register_style( 'tccss-head', false );
			wp_enqueue_style( 'tccss-head' );
			wp_add_inline_style( 'tccss-head', $criticalcss->data->css );
		}
		
		// add it to the footer instead
		add_action( 'get_footer', function() {
			
			// enqueue stuff
			$adminmode = tccss()->options()->get( 'adminmode' );
			$logged_in   = is_user_logged_in();
			$styles    = tccss()->sheetlist()->get_selected();
			foreach ( $styles as $handle => $url ) {
				if ( $adminmode == true && ! $logged_in ) {
					?><!-- TCSSS: enqueue: ( <?php echo esc_html( $handle ); ?> ): <?php echo esc_url( $url ); ?> -->
<?php
				} else {
					wp_enqueue_style( $handle, $url, false, null, 'all' );
				}
			}
			
		} );
		
	}

}
