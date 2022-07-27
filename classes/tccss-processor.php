<?php

class TCCSS_Processor {
	
	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
		add_action( 'process_critical_css', array( $this, 'process_critical_css' ), 10, 3 );
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
		$type        = $this->get_route_type();
		$route_or_id = $this->get_route_or_id();
		$data        = tccss()->options()->getmeta( $type, $route_or_id, 'criticalcss' );
		$invalidate  = tccss()->options()->getmeta( $type, $route_or_id, 'invalidate' );
		$retry       = tccss()->options()->getmeta( $type, $route_or_id, 'retry' );
		
		// data does not exist, or data exists and did not succees, or invalidation flag is set
		if ( ! $data || $data->success !== true || $invalidate == 'loading' || $retry > 0 ) {
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
		
		$type         = $this->get_route_type();
		$route_or_id  = $this->get_route_or_id();
		
		$request_uri  = $this->get_request_uri();
		$route_actual = $type == 'route' ? home_url( $route_or_id ) : get_permalink( $route_or_id );
		$route_label  = $route_actual;
		$route_label  = $type == 'route' ? $route_label : $route_or_id . ', ' . $route_label;
		
		$invalidate   = tccss()->options()->getmeta( $type, $route_or_id, 'invalidate' );
		$checksum     = tccss()->options()->getmeta( $type, $route_or_id, 'checksum' );
		$retry        = tccss()->options()->getmeta( $type, $route_or_id, 'retry' );
		$retry_at     = tccss()->options()->getmeta( $type, $route_or_id, 'retry_at' );
		$sheetlist    = tccss()->sheetlist()->get_checksum();
		
		tccss()->log( 'Validate url: ' . $route_label, $type, $route_or_id );
		tccss()->log( 'Current invalidate flag: ' . ( $invalidate ? $invalidate : 'false' ), $type, $route_or_id );
		tccss()->log( 'Checksum: ' . ( $checksum ? $checksum : 'false' ), $type, $route_or_id );
		tccss()->log( 'Sheetlist: ' . ( $sheetlist ? $sheetlist : 'false' ), $type, $route_or_id );
		
		// already running? skip it
		if ( $invalidate == 'loading' ) {
			tccss()->log( 'Invalidation stopped.  Already Loading.', $type, $route_or_id );
			return;
		}
		
		// always check to see if the checksum is different that the current one
		// or if there is no checksum ( meaning it's never been run )
		// if so, run it again
		if ( $checksum != $sheetlist || ! $checksum ) {
			$invalidate = true;
		}
		
		// if we are about to run it, check to see if it's a retry
		if ( $invalidate && $retry ) {
			tccss()->log( 'Retry Attempt: ' . $retry, $type, $route_or_id );
			if ( time() < $retry_at ) {
				tccss()->log( 'Retry At: ' . $retry_at . ", Current time: " . time() . ' ( ' . ( $retry_at - time() ) . 's )', $type, $route_or_id );
				$invalidate = false;
			}
		}
		
		tccss()->log( 'Final invalidate flag: ' . ( $invalidate ? 'true' : 'false' ), $type, $route_or_id );
		
		// if not invalidated, stop right here.
		if ( ! $invalidate ) {
			return;
		}

		// pull data we need
		$simplemode    = tccss()->options()->get( 'simplemode' );
		$custom_routes = tccss()->options()->get( 'custom_routes', [] );
		$ignore_routes = tccss()->options()->get( 'ignore_routes', [] );
		$selected_cpt  = $simplemode ? [ 'page', 'post', 'product' ] : tccss()->options()->get( 'selected_cpt', [] );
		
		// ignore?
		foreach ( $ignore_routes as $ignore_route ) {
			$ignore = $this->url_matches( $ignore_route, $request_uri );
			if ( $ignore ) {
				tccss()->log( 'Invalidation stopped.  Route ' . $request_uri . ' Ignored.', $type, $route_or_id );
				return;
			}
		}
		
		// should we process this route?
		$process = $simplemode;
		if ( $process == false ) { // simple mode off
			if ( $type == 'route' ) {
				foreach ( $custom_routes as $custom_route ) {
					$process = $this->url_matches( $custom_route, $request_uri );
					if ( $process == true ) {
						break;
					}
				}
			} else {
				$process = in_array( get_post_type(), $selected_cpt );
			}
		}
		if ( $process == false ) {
			tccss()->log( 'Invalidation stopped.  Route ' . $route_actual . ' not processed.', $type, $route_or_id );
			return;
		}
		
		// set the flag to show we are loading
		$invalidate_hash = md5( uniqid( '', true ) . $route_or_id );
		tccss()->log( 'Invalidation loading ' . $invalidate_hash . '...', $type, $route_or_id );
		tccss()->options()->setmeta( $type, $route_or_id, 'invalidate', 'loading' );
		tccss()->options()->setmeta( $type, $route_or_id, 'invalidate_hash', $invalidate_hash );
		tccss()->options()->setmeta( $type, $route_or_id, 'invalidate_start', time() );
		
		// pull the critical css
		$scheduled = wp_schedule_single_event( time() + rand( 30, 90 ), 'process_critical_css', [ $type, $route_or_id, $invalidate_hash ] );
		tccss()->log( 'CRON scheduling ' . ( $scheduled ? 'succeeded!' : 'failed!' ), $type, $route_or_id );
		
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
	public function process_critical_css( $type, $route_or_id, $invalidate_hash ) {
		
		$route_actual = $type == 'route' ? home_url( $route_or_id ) : get_permalink( $route_or_id );
		$route_label  = $route_actual;
		$route_label  = $type == 'route' ? $route_label : $route_or_id . ', ' . $route_label;
		
		if ( $invalidate_hash != tccss()->options()->getmeta( $type, $route_or_id, 'invalidate_hash' ) ) {
			tccss()->log( 'Invalidation hash ' . $invalidate_hash . ' does not exist.  Ignoring CRON call', $type, $route_or_id );
			return;
		}
		
		// do the actual call
		tccss()->log( 'Critical CSS loading: ' . $route_label, $type, $route_or_id );
		$response_data = $this->get_critical_css( $route_actual, $type, $route_or_id );
		$json_data     = json_decode( $response_data );
		tccss()->options()->setmeta( $type, $route_or_id, 'criticalcss', $json_data );
		
		// save the global checksum at this point in time to check in the future
		if ( $json_data->success === true ) {
			tccss()->options()->setmeta( $type, $route_or_id, 'checksum', tccss()->sheetlist()->get_checksum() );
			tccss()->log( 'Critical CSS for ' . $route_label . ' loaded successfully!', $type, $route_or_id );
			tccss()->options()->setmeta( $type, $route_or_id, 'retry', false );
			tccss()->options()->setmeta( $type, $route_or_id, 'retry_at', false );
		} else {
			$retry = tccss()->options()->getmeta( $type, $route_or_id, 'retry', 0 );
			$retry += 1;
			tccss()->options()->setmeta( $type, $route_or_id, 'retry', $retry );
			tccss()->options()->setmeta( $type, $route_or_id, 'retry_at', time() + ( $retry * 120 ) );
			tccss()->log( 'Critical CSS for ' . $route_label . ' failed! Retry: ' . $retry, $type, $route_or_id );
		}
		
		// clear out any invalidation flags
		tccss()->options()->setmeta( $type, $route_or_id, 'invalidate', false );
		tccss()->options()->setmeta( $type, $route_or_id, 'invalidate_hash', false );
		tccss()->options()->setmeta( $type, $route_or_id, 'invalidate_start', false );
		
	}
	
	/**
	 * get_critical_css
	 *
	 * Call the server API and return the result.
	 *
	 * @param   string $page_url The page url to generate critical css for.
	 * @return  string The json string body from the response.
	 */
	public function get_critical_css( $page_url, $type, $route_or_id ) {
		
		// get all the styles and concatenate
		$simplemode      = tccss()->options()->get( 'simplemode' );
		$penthouse_props = tccss()->options()->get( 'penthouse_props' );
		$api_key         = tccss()->options()->get( 'api_key' );
		$stylesheets     = tccss()->sheetlist()->get_selected();
		
		$css             = [];
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
			'p' => ( $simplemode ? '' : json_encode( $penthouse_props ) ),
			'k' => $api_key,
			't' => md5( uniqid( '', true ) ),
		];
		
		tccss()->log( 'get_critical_css: ' . $uri, $type, $route_or_id );
		tccss()->log( $query, $route_or_id );

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
		if ( empty( $request_uri ) && isset( $_SERVER['REQUEST_URI'] ) ) {
			$request_uri = filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL, $options );
		}
		$request_uri = parse_url( $request_uri, PHP_URL_PATH );
		if ( $request_uri !== '/' ) {
			$request_uri = trim( $request_uri, '/' );
		}
		return rawurldecode( $request_uri );
	}
	
	/**
	 * Gets the quest uri, with fallback for super global
	 *
	 * @return boolean
	 */
	function url_matches( $pattern, $search ) {
		// just check for strict equality first
		$result = $pattern === $search;
		tccss()->log( 'Strict equality "' . $pattern . '" and "' . $search . '" ... ' . ( $result !== false ? 'Matched!' : 'Skipped.' ) );
		if ( $result !== false ) {
			return true;
		}
		// if either is the root, go no further
		if ( $pattern == '/' || $search == '/' ) {
			return false;
		}
		// lets try regex or string-in-string
		if ( stripos( $pattern, '*' ) !== false || stripos( $pattern, '^' ) !== false ) {
			// regex string detected
			$pattern = str_replace( '`', '\\`', $pattern );
			$pattern = "`{$pattern}`";
			$result = @preg_match( $pattern, $search, $url_matches );
			tccss()->log( 'Regex string pattern "' . $pattern . '" and subject "' . $search . '" ... ' . ( 1 === $result ? 'Matched!' : 'Skipped.' ) );
			if ( 1 === $result ) {
				//print_r( $url_matches );
				return true;
			}
		} else {
			// otherwise, do a simple string in string search compare -- is one in the other?
			$result = stripos( $pattern, $search );
			tccss()->log( 'Non-regex string pattern "' . $pattern . '" and subject "' . $search . '" ... ' . ( $result !== false ? 'Matched!' : 'Skipped.' ) );
			if ( $result !== false ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * get_route_or_id
	 *
	 * Return the checksum.
	 *
	 * @param   void
	 * @return  mixed The route or ID.
	 */
	public function get_route_or_id() {
		if ( $this->get_route_type() == 'route' ) {
			return $this->get_request_uri();
		} else {
			return get_the_ID();
		}
	}
	
	/**
	 * get_route_type
	 *
	 * Return the checksum.
	 *
	 * @param   void
	 * @return  string The route type.
	 */
	public function get_route_type() {
		if ( is_singular() || is_page() || is_single() ) {
			return 'single';
		} else {
			return 'route';
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
		$type        = $this->get_route_type();
		$route_or_id = $this->get_route_or_id();
		return tccss()->options()->getmeta( $type, $route_or_id, 'criticalcss' );
	}

}
