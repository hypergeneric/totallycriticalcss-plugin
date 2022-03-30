<?php

class TCCSS_Processor {
	
	public function validate() {
		
		global $wp;
		
		$simplemode = tccss()->options()->get( 'simplemode' );
		$invalidate = $this->get_invalidate();
		$checksum   = $this->get_checksum();
		
		tccss()->log( 'validate: ' . ( is_archive() ? $wp->request : get_the_ID() ) );
		tccss()->log( 'invalidate: ' . $invalidate );
		tccss()->log( 'checksum: ' . $checksum );
		
		// if it's simple mode, always check to see if the checksum is different that the current one
		// if so, run it again
		if ( $simplemode ) {
			if ( $checksum != get_transient( 'totallycriticalcss-sheetlist-checksum' ) ) {
				$invalidate = true;
			}
		}
		
		// get the critical css
		if ( $invalidate ) {
			$this->process();
		}
		
	}
	
	public function processed() {
		
		// only do the dequeueing / enqueuing if the data is live
		$data = $this->get_data();
		$invalidate = $this->get_invalidate();
		
		if ( ! $data || $data->success !== true || $invalidate == 'loading' ) {
			return false;
		}
		
		return true;
		
	}
	
	public function process() {
		global $wp;
		if ( is_archive() ) {
			$this->archive( $wp->request );
		} else {
			$this->single( get_the_ID() );
		}
	}
	
	public function set_checksum( $value ) {
		if ( is_archive() ) {
			$this->set_route_value( 'checksum', $value );
		} else {
			tccss()->options()->setmeta( get_the_ID(), 'checksum', $value );
		}
	}
	
	public function get_checksum() {
		if ( is_archive() ) {
			return $this->get_route_value( 'checksum' );
		} else {
			return tccss()->options()->getmeta( get_the_ID(), 'checksum' );
		}
	}
		
	public function set_invalidate( $value ) {
		if ( is_archive() ) {
			$this->set_route_value( 'invalidate', $value );
		} else {
			tccss()->options()->setmeta( get_the_ID(), 'invalidate', $value );
		}
	}
	
	public function get_invalidate() {
		if ( is_archive() ) {
			return $this->get_route_value( 'invalidate' );
		} else {
			return tccss()->options()->getmeta( get_the_ID(), 'invalidate' );
		}
	}
	
	public function set_data( $value ) {
		if ( is_archive() ) {
			$this->set_route_value( 'criticalcss', $value );
		} else {
			tccss()->options()->setmeta( get_the_ID(), 'criticalcss', $value );
		}
	}
	
	public function get_data() {
		if ( is_archive() ) {
			return $this->get_route_value( 'criticalcss' );
		} else {
			return tccss()->options()->getmeta( get_the_ID(), 'criticalcss' );
		}
	}
	
	public function set_route_value( $name, $value ) {
		global $wp;
		$route_data = tccss()->options()->get( 'route_data', [] );
		if ( ! isset( $route_data[$wp->request] ) ) {
			$route_data[$wp->request] = [];
		}
		$route_data[$wp->request][$name] = $value;
		tccss()->options()->set( 'route_data', $route_data, false );
	}
	
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
	
	public function get_critical_css( $page_url ) {
		
		// get all the styles and concatenate
		$stylesheets = tccss()->sheetlist()->get_selected();
		$css = [];
		foreach ( $stylesheets as $handle => $url ) {
			$css[] = $url;
		}
		$css = implode( '::::', $css );
		$css = str_replace( 'totallycritical.lndo.site', 'totallycriticalcss.com', $css );
		
		// get the post url
		$page_url = str_replace( 'totallycritical.lndo.site', 'totallycriticalcss.com', $page_url );
		
		// generate the url
		$uri = 'http://api.totallycriticalcss.com/v1/';
		$query = [
			'u' => $page_url,
			'c' => $css,
			'k' => get_option( 'totallycriticalcss_api_key' ),
			't' => md5( uniqid( '', true ) ),
		];

		// pull the critical and return it
		$response      = wp_remote_get( $uri . "?" . http_build_query( $query ), [ 'timeout' => 30 ] );
		$response_data = ! is_wp_error( $response ) ? $response['body'] : '{"success":false,"message":"' . addslashes( $response->get_error_message() ) . '"}';
		
		return $response_data;
		
	}
	
	public function archive( $route ) {
		
		tccss()->log( 'get_totallycriticalcss: ' . $route );
		
		$simplemode    = tccss()->options()->get( 'simplemode' );
		$custom_routes = tccss()->options()->get( 'custom_routes', [] );
		
		// should we process this route?
		$process_route = $simplemode;
		if ( $process_route == false ) {
			foreach ( $custom_routes as $custom_route ) {
				if ( strtolower( trim( $custom_route, '/' ) ) == strtolower( trim( $route, '/' ) ) ) {
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
		$this->set_data( json_decode( $response_data ) );
		
		// if it's simple mode, save the global checksum at this point in time to check in the future
		if ( $simplemode ) {
			$this->set_checksum( get_transient( 'totallycriticalcss-sheetlist-checksum' ) );
		}
		
		// clear out any invalidation flag
		tccss()->log( 'get_totallycriticalcss: ' . $route . ' loaded!' );
		$this->set_invalidate( false );
		
	}
	
	public function single( $id ) {
		
		tccss()->log( 'get_totallycriticalcss: ' . $id );
		
		$simplemode = tccss()->options()->get( 'simplemode' );
		$invalidate = tccss()->options()->getmeta( $id, 'invalidate' );
		
		if ( $invalidate == 'loading' ) {
			return;
		}
		
		// clear out any invalidation flag
		tccss()->log( 'get_totallycriticalcss: ' . $id . ' loading' );
		tccss()->options()->setmeta( $id, 'invalidate', 'loading' );
		
		// pull the critical css
		$response_data = $this->get_critical_css( get_permalink( $id ) );
		tccss()->options()->setmeta( $id, 'criticalcss', json_decode( $response_data ) );
		
		// if it's simple mode, save the global checksum at this point in time to check in the future
		if ( $simplemode ) {
			tccss()->options()->setmeta( $id, 'checksum', get_transient( 'totallycriticalcss-sheetlist-checksum' ) );
		}
		
		// clear out any invalidation flag
		tccss()->log( 'get_totallycriticalcss: ' . $id . ' loaded!' );
		tccss()->options()->setmeta( $id, 'invalidate', false );
		
	}

}
