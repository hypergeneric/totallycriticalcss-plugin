<?php

class TCCSS_Processor {
	
	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
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
		
		global $wp;
		
		$invalidate = $this->get_invalidate();
		$checksum   = $this->get_checksum();
		
		tccss()->log( 'validate: ' . ( is_archive() ? $wp->request : get_the_ID() ) );
		tccss()->log( 'invalidate: ' . ( $invalidate ? 'true' : 'false' ) );
		tccss()->log( 'checksum: ' . ( $checksum ? $checksum : 'false' ) );
		tccss()->log( 'sheetlist: ' . ( tccss()->sheetlist()->get_checksum() ? tccss()->sheetlist()->get_checksum() : 'false' ) );
		
		// always check to see if the checksum is different that the current one
		// if so, run it again
		if ( $checksum != tccss()->sheetlist()->get_checksum() || ! $checksum ) {
			$invalidate = true;
		}
		
		tccss()->log( 'invalidate: ' . ( $invalidate ? 'true' : 'false' ) );
		
		// get the critical css
		if ( $invalidate ) {
			$this->process();
		}
		
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
		
		if ( ! $data || $data->success !== true || $invalidate == 'loading' ) {
			return false;
		}
		
		return true;
		
	}
	
	/**
	 * process
	 *
	 * Start the critical css processing by determining the type of page.
	 *
	 * @param   void
	 * @return  void
	 */
	public function process() {
		global $wp;
		if ( is_archive() ) {
			$this->archive( $wp->request );
		} else {
			$this->single( get_the_ID() );
		}
	}
	
	/**
	 * set_checksum
	 *
	 * Set the checksum.
	 *
	 * @param   string $value The new checksum.
	 * @return  void
	 */
	public function set_checksum( $value ) {
		if ( is_archive() ) {
			$this->set_route_value( 'checksum', $value );
		} else {
			tccss()->options()->setmeta( get_the_ID(), 'checksum', $value );
		}
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
			return $this->get_route_value( 'checksum' );
		} else {
			return tccss()->options()->getmeta( get_the_ID(), 'checksum' );
		}
	}
	
	/**
	 * set_invalidate
	 *
	 * Set the validation token.
	 *
	 * @param   string $value The new validation token.
	 * @return  void
	 */
	public function set_invalidate( $value ) {
		if ( is_archive() ) {
			$this->set_route_value( 'invalidate', $value );
		} else {
			tccss()->options()->setmeta( get_the_ID(), 'invalidate', $value );
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
			return $this->get_route_value( 'invalidate' );
		} else {
			return tccss()->options()->getmeta( get_the_ID(), 'invalidate' );
		}
	}
	
	/**
	 * set_data
	 *
	 * Set the critical css data.
	 *
	 * @param   string $value The new critical css data.
	 * @return  void
	 */
	public function set_data( $value ) {
		if ( is_archive() ) {
			$this->set_route_value( 'criticalcss', $value );
		} else {
			tccss()->options()->setmeta( get_the_ID(), 'criticalcss', $value );
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
			return $this->get_route_value( 'criticalcss' );
		} else {
			return tccss()->options()->getmeta( get_the_ID(), 'criticalcss' );
		}
	}
	
	/**
	 * set_route_value
	 *
	 * Set data objects based on route / id.
	 *
	 * @param   string $name The data name.
	 * @param   mixed $value The data value.
	 * @return  void
	 */
	public function set_route_value( $name, $value ) {
		global $wp;
		$route_data = tccss()->options()->get( 'route_data', [] );
		if ( ! isset( $route_data[$wp->request] ) ) {
			$route_data[$wp->request] = [];
		}
		$route_data[$wp->request][$name] = $value;
		tccss()->options()->set( 'route_data', $route_data, false );
	}
	
	/**
	 * get_route_value
	 *
	 * Return data objects based on route / id.
	 *
	 * @param   string $name The data name.
	 * @return  mixed The data value
	 */
	public function get_route_value( $name ) {
		global $wp;
		$route_data = tccss()->options()->get( 'route_data', [] );
		if ( isset( $route_data[$wp->request] ) ) {
			if ( isset( $route_data[$wp->request][$name] ) ) {
				return $route_data[$wp->request][$name];
			}
			return false;
		}
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
			$css[] = apply_filters( "tccss_parse_internal_uri", $url );
		}
		$css = implode( '::::', $css );
		
		// get the post url
		$page_url = apply_filters( "tccss_parse_internal_uri", $page_url );
		
		// generate the url
		$uri = 'http://api.totallycriticalcss.com/v1/';
		$query = [
			'u' => $page_url,
			'c' => $css,
			'k' => tccss()->options()->get( 'api_key' ),
			'd' => tccss()->options()->get( 'simplemode' ) == true ? '1' : '0',
			't' => md5( uniqid( '', true ) ),
		];
		
		tccss()->log( $query );

		// pull the critical and return it
		$response      = wp_remote_get( $uri . "?" . http_build_query( $query ), [ 'timeout' => 30 ] );
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
	 * archive
	 *
	 * Process an archive page.
	 *
	 * @param   string $route The full url to generate.
	 * @return  void
	 */
	public function archive( $route ) {
		
		tccss()->log( 'get_totallycriticalcss: ' . $route );
		
		$simplemode    = tccss()->options()->get( 'simplemode' );
		$custom_routes = tccss()->options()->get( 'custom_routes', [] );
		$ignore_routes = tccss()->options()->get( 'ignore_routes', [] );
		
		// should we process this route?
		$request_url   = $this->get_request_uri();
		$process_route = $simplemode;
		foreach ( $ignore_routes as $ignore_route ) {
			$ignore = $this->url_matches( $ignore_route, $request_url );
			if ( $ignore ) {
				return;
			}
		}
		
		if ( $process_route == false ) {
			foreach ( $custom_routes as $custom_route ) {
				$process = $this->url_matches( $custom_route, $request_url );
				if ( $process == true && $process_route == false ) {
					$process_route = true;
				}
			}
		}
		if ( $process_route == false ) {
			return;
		}
		
		$invalidate = $this->get_invalidate();
		if ( $invalidate == 'loading' ) {
			return;
		}
		
		// clear out any invalidation flag
		tccss()->log( 'get_totallycriticalcss: ' . $route . ' loading' );
		$this->set_invalidate( 'loading' );
		
		// pull the critical css
		$response_data = $this->get_critical_css( home_url( $route ) );
		$json_data = json_decode( $response_data );
		$this->set_data( $json_data );
		
		// if it's simple mode, save the global checksum at this point in time to check in the future
		if ( $json_data->success === true ) {
			$this->set_checksum( tccss()->sheetlist()->get_checksum() );
		}
		
		// clear out any invalidation flag
		tccss()->log( 'get_totallycriticalcss: ' . $route . ' loaded!' );
		$this->set_invalidate( false );
		
	}
	
	/**
	 * single
	 *
	 * Process an single page.
	 *
	 * @param   string $id The post ID.
	 * @return  void
	 */
	public function single( $id ) {
		
		tccss()->log( 'get_totallycriticalcss: ' . $id );
		
		$simplemode = tccss()->options()->get( 'simplemode' );
		$ignore_routes = tccss()->options()->get( 'ignore_routes', [] );
		
		// should we process this route?
		$request_url = $this->get_request_uri();
		foreach ( $ignore_routes as $ignore_route ) {
			$ignore = $this->url_matches( $ignore_route, $request_url );
			if ( $ignore ) {
				return;
			}
		}
		
		// is it already running?
		$invalidate = tccss()->options()->getmeta( $id, 'invalidate' );
		if ( $invalidate == 'loading' ) {
			return;
		}
		
		// clear out any invalidation flag
		tccss()->log( 'get_totallycriticalcss: ' . $id . ' loading' );
		tccss()->options()->setmeta( $id, 'invalidate', 'loading' );
		
		// pull the critical css
		$response_data = $this->get_critical_css( get_permalink( $id ) );
		$json_data = json_decode( $response_data );
		tccss()->options()->setmeta( $id, 'criticalcss', $json_data );
		
		// if it's simple mode, save the global checksum at this point in time to check in the future
		if ( $json_data->success === true ) {
			tccss()->options()->setmeta( $id, 'checksum', tccss()->sheetlist()->get_checksum() );
		}
		
		// clear out any invalidation flag
		tccss()->log( 'get_totallycriticalcss: ' . $id . ' loaded!' );
		tccss()->options()->setmeta( $id, 'invalidate', false );
		
	}

}
