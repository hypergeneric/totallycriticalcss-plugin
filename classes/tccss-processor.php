<?php

class TCCSS_Processor {
	
	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
		add_action( 'process_critical_css', array( $this, 'process_critical_css' ), 10, 2 );
	}
	
	/**
	 * processed
	 *
	 * Determine if the page has critical css and is done loading.
	 *
	 * @param   void
	 * @return  boolean if it's ready to go.
	 */
	public function processed() {
		
		// only do the dequeueing / enqueuing if the data is live
		$data = $this->get_data();
		$invalidate = $this->get_invalidate();
		
		// data does not exist, or data exists and did not succees, or invalidation flag is set
		if ( ! $data || $data->success !== true || $invalidate == 'loading' ) {
			return false;
		}
		
		return true;
		
	}
	
	/**
	 * validate
	 *
	 * Check for page validation and/or reprocessing.
	 *
	 * @param   void
	 * @return  void
	 */
	public function validate() {
		
		$invalidate = $this->get_invalidate();
		$checksum   = $this->get_checksum();
		
		tccss()->log( 'Validate url: ' . ( is_archive() ? $this->get_request_uri() : get_the_ID() . ", " . get_permalink( get_the_ID() ) ) );
		tccss()->log( 'Current invalidate flag: ' . ( $invalidate ? 'true' : 'false' ) );
		tccss()->log( 'Checksum: ' . ( $checksum ? $checksum : 'false' ) );
		tccss()->log( 'Sheetlist: ' . ( tccss()->sheetlist()->get_checksum() ? tccss()->sheetlist()->get_checksum() : 'false' ) );
		
		// always check to see if the checksum is different that the current one
		// or if there is no checksum ( meaning it's never been run )
		// if so, run it again
		if ( $checksum != tccss()->sheetlist()->get_checksum() || ! $checksum ) {
			$invalidate = true;
		}
		
		tccss()->log( 'Final invalidate flag: ' . ( $invalidate ? 'true' : 'false' ) );
		
		// get the critical css
		if ( $invalidate ) {
			$this->lets_get_critical();
		}
		
	}
	
	/**
	 * archive
	 *
	 * Process an archive page.
	 *
	 * @param   string $route The full url to generate.
	 * @return  void
	 */
	public function lets_get_critical() {
		
		$type         = is_archive() ? 'route' : 'single';
		$route_or_id  = $type == 'route' ? $this->get_request_uri() : get_the_ID();
		$route_actual = $this->get_request_uri();
		$route_label  = $route_actual == '/' ? $route_actual : '/' . $route_actual . '/';
		$route_label  = $type == 'route' ? $route_label : get_the_ID() . ', ' . $route_label;
		
		tccss()->log( 'lets_get_critical: ' . $route_label );
		
		// already running? skip it
		$invalidate = tccss()->options()->getmeta( $type, $route_or_id, 'invalidate' );
		if ( $invalidate == 'loading' ) {
			tccss()->log( 'lets_get_critical: ' . $route_label . ' stopped.  Already Loading.' );
			return;
		}
		
		// pull data we need
		$simplemode    = tccss()->options()->get( 'simplemode' );
		$custom_routes = tccss()->options()->get( 'custom_routes', [] );
		$ignore_routes = tccss()->options()->get( 'ignore_routes', [] );
		
		// ignore?
		foreach ( $ignore_routes as $ignore_route ) {
			$ignore = $this->url_matches( $ignore_route, $route_actual );
			if ( $ignore ) {
				tccss()->log( 'lets_get_critical: ' . $route_label . ' stopped.  Ignored.' );
				return;
			}
		}
		
		// should we process this route?
		if ( $type == 'route' ) {
			$process_route = $simplemode;
			if ( $process_route == false ) {
				foreach ( $custom_routes as $custom_route ) {
					$process = $this->url_matches( $custom_route, $route_actual );
					if ( $process == true && $process_route == false ) {
						$process_route = true;
					}
				}
			}
			if ( $process_route == false ) {
				tccss()->log( 'lets_get_critical: ' . $route_label . ' stopped.  Route not processed.' );
				return;
			}
		}
		
		// set the flag to show we are loading
		tccss()->log( 'lets_get_critical: ' . $route_label . ' loading' );
		tccss()->options()->setmeta( $type, $route_or_id, 'invalidate', 'loading' );
		
		// pull the critical css
		wp_schedule_single_event( time() + 30, 'process_critical_css', [ $type, $route_or_id ] );
		
	}
	
	/**
	 * process_critical_css
	 *
	 * Call the server API and return the result.
	 *
	 * @param   string $type The page type
	 * @param   string $route_or_id The page route or id if it's single
	 * @return  string The json string body from the response.
	 */
	public function process_critical_css( $type, $route_or_id ) {
		
		$route_actual = $type == 'route' ? home_url( $route_or_id ) : get_permalink( $route_or_id );
		$route_label  = $route_actual;
		$route_label  = $type == 'route' ? $route_label : get_the_ID() . ', ' . $route_label;
		
		// do the actual call
		$response_data = $this->get_critical_css( $route_actual );
		$json_data     = json_decode( $response_data );
		tccss()->options()->setmeta( $type, $route_or_id, 'criticalcss', $json_data );
		
		// save the global checksum at this point in time to check in the future
		if ( $json_data->success === true ) {
			tccss()->options()->setmeta( $type, $route_or_id, 'checksum', tccss()->sheetlist()->get_checksum() );
		}
		
		// clear out any invalidation flag
		tccss()->log( 'process_critical_css: ' . $route_label . ' loaded!' );
		tccss()->options()->setmeta( $type, $route_or_id, 'invalidate', false );
		
	}
	
	/**
	 * get_critical_css
	 *
	 * Call the server API and return the result.
	 *
	 * @param   string $page_url The page url to generate critical css for.
	 * @return  string The json string body from the response.
	 */
	public function get_critical_css( $page_url ) {
		
		// get all the styles and concatenate
		$stylesheets = tccss()->sheetlist()->get_selected();
		$css = [];
		foreach ( $stylesheets as $handle => $url ) {
			$css[] = apply_filters( 'tccss_parse_internal_uri', $url );
		}
		$css = implode( '::::', $css );
		
		// get the post url
		$page_url = apply_filters( 'tccss_parse_internal_uri', $page_url );
		
		// generate the url
		$uri = 'https://api.totallycriticalcss.com/v1/';
		if ( defined( 'TCCSS_USE_STAGING' ) && TCCSS_USE_STAGING ) {
			$uri = 'https://staging-api.totallycriticalcss.com/v1/';
		}
		$query = [
			'u' => $page_url,
			'c' => $css,
			'k' => tccss()->options()->get( 'api_key' ),
			'd' => tccss()->options()->get( 'simplemode' ) == true ? '1' : '0',
			't' => md5( uniqid( '', true ) ),
		];
		
		tccss()->log( 'get_critical_css: ' . $uri );
		tccss()->log( $query );

		// pull the critical and return it
		$response      = wp_remote_get( $uri . '?' . http_build_query( $query ), [ 'timeout' => 30 ] );
		$response_data = ! is_wp_error( $response ) ? $response['body'] : '{"success":false,"message":"' . addslashes( $response->get_error_message() ) . '"}';
		
		return $response_data;
		
	}
	
	/**
	 * Gets the quest uri, with fallback for super global
	 *
	 * @return string
	 */
	function get_request_uri() {
		$options     = array( 'options' => array( 'default' => '' ) );
		$request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL, $options );
		// Because there isn't an usable value, try the fallback.
		if ( empty( $request_uri ) && isset( $_SERVER['REQUEST_URI'] ) ) {
			$request_uri = filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL, $options );
		}
		if ( $request_uri !== '/' ) {
			$request_uri = trim( $request_uri, '/' );
		}
		return rawurldecode( $request_uri );
	}
	
	/**
	 * Gets the quest uri, with fallback for super global
	 *
	 * @return string
	 */
	function url_matches( $search, $url ) {
		if ( strtolower( $search ) == strtolower( $url ) ) {
			return true;
		}
		$search = str_replace( '`', '\\`', $search );
		// Suppress warning: a faulty redirect will give a warning and not an exception. So we can't catch it.
		// See issue: https://github.com/Yoast/wordpress-seo-premium/issues/662.
		if ( 1 === @preg_match( "`{$search}`", $url, $url_matches ) ) {
			//print_r( $url_matches );
			return true;
		}
		return false;
	}
	
	/**
	 * get_checksum
	 *
	 * Return the checksum.
	 *
	 * @param   void
	 * @return  string The saved checksum.
	 */
	public function get_checksum() {
		if ( is_archive() ) {
			return tccss()->options()->getroutemeta( $this->get_request_uri(), 'checksum');
		} else {
			return tccss()->options()->getpostmeta( get_the_ID(), 'checksum' );
		}
	}
	
	/**
	 * get_invalidate
	 *
	 * Return the validation token.
	 *
	 * @param   void
	 * @return  mixed The saved validation token.
	 */
	public function get_invalidate() {
		if ( is_archive() ) {
			return tccss()->options()->getroutemeta( $this->get_request_uri(), 'invalidate');
		} else {
			return tccss()->options()->getpostmeta( get_the_ID(), 'invalidate' );
		}
	}
	
	/**
	 * get_data
	 *
	 * Return the critical css data.
	 *
	 * @param   void
	 * @return  object The saved critical css data.
	 */
	public function get_data() {
		if ( is_archive() ) {
			return tccss()->options()->getroutemeta( $this->get_request_uri(), 'criticalcss');
		} else {
			return tccss()->options()->getpostmeta( get_the_ID(), 'criticalcss' );
		}
	}

}
