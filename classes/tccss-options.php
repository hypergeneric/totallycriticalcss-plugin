<?php

class TCCSS_Options {
	
	/** @var string Local array to store lookups. */
	var $lookup = [];

	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
	}
	
	/**
	 * get_penthouse_types
	 *
	 * Add a property to the penthouse json object
	 *
	 * @param   void
	 * @return  array
	 */
	public function get_penthouse_types() {
		return [
			'width' => [ 'type' => 'number', 'default' => 1400 ],
			'height' => [ 'type' => 'number', 'default' => 1000 ],
			'keepLargerMediaQueries' => [ 'type' => 'boolean', 'default' => false ],
			'forceInclude' => [ 'type' => 'array', 'default' => [] ],
			'forceExclude' => [ 'type' => 'array', 'default' => [] ],
			'propertiesToRemove' => [ 'type' => 'array', 'default' => [] ],
			'timeout' => [ 'type' => 'number', 'default' => 30000 ],
			'pageLoadSkipTimeout' => [ 'type' => 'number', 'default' => 0 ],
			'renderWaitTime' => [ 'type' => 'number', 'default' => 100 ],
			'blockJSRequests' => [ 'type' => 'boolean', 'default' => true ],
			'maxEmbeddedBase64Length' => [ 'type' => 'number', 'default' => 1000 ],
			'maxElementsToCheckPerSelector' => [ 'type' => 'number', 'default' => null ],
			'userAgent' => [ 'type' => 'string', 'default' => '' ],
			'customPageHeaders' => [ 'type' => 'array', 'default' => [] ],
			'cookies' => [ 'type' => 'array', 'default' => [] ],
			'strict' => [ 'type' => 'boolean', 'default' => false ],
		];
	}
	
	/**
	 * getmeta
	 *
	 * Sugar function to save post meta.
	 *
	 * @param   string $type The route type.
	 * @param   string $route_or_id The route url.
	 * @param   string $name The meta name.
	 * @return  mixed The meta value
	 */
	public function getmeta( $type, $route_or_id, $name, $default=null ) {
		if ( $type == 'route' ) {
			return $this->getroutemeta( $route_or_id, $name, $default );
		} else {
			return $this->getpostmeta( $route_or_id, $name, $default );
		}
	}
	
	/**
	 * setmeta
	 *
	 * Sugar function to save post meta.
	 *
	 * @param   string $type The route type.
	 * @param   string $route_or_id The route url.
	 * @param   string $name The meta name.
	 * @return  mixed The meta value
	 */
	public function setmeta( $type, $route_or_id, $name, $value ) {
		if ( $type == 'route' ) {
			return $this->setroutemeta( $route_or_id, $name, $value );
		} else {
			return $this->setpostmeta( $route_or_id, $name, $value );
		}
	}
	
	/**
	 * getroutemeta
	 *
	 * Sugar function to save post meta.
	 *
	 * @param   string $route The route url.
	 * @param   string $name The meta name.
	 * @return  mixed The meta value
	 */
	public function getroutemeta( $route, $name, $default=null ) {
		$option_name = 'route_' . trim( str_replace( '/', '_', $route ), '/' );
		$route_data  = $this->get( $option_name, [] );
		return isset( $route_data[$name] ) ? $route_data[$name] : $default;
	}
	
	/**
	 * setroutemeta
	 *
	 * Sugar function to save post meta.
	 *
	 * @param   string $route The route url.
	 * @param   string $name The meta name.
	 * @return  mixed The meta value
	 */
	public function setroutemeta( $route, $name, $value ) {
		$option_name = 'route_' . trim( str_replace( '/', '_', $route ), '/' );
		$route_data  = $this->get( $option_name, [] );
		$route_data[$name] = $value;
		$this->set( $option_name, $route_data );
	}
	
	/**
	 * getpostmeta
	 *
	 * Sugar function to save post meta.
	 *
	 * @param   int $id The post id.
	 * @param   string $name The meta name.
	 * @return  mixed The meta value
	 */
	public function getpostmeta( $id, $name, $default=null ) {
		if ( isset( $this->lookup[$id] ) ) {
			if ( isset( $this->lookup[$id][$name] ) ) {
				return $this->lookup[$id][$name];
			}
		}
		$value = get_post_meta( $id, 'totallycriticalcss_' . $name, true );
		return $value ? $value : $default;
	}
	
	/**
	 * setpostmeta
	 *
	 * Sugar function to save post meta.
	 *
	 * @param   int $id The post id.
	 * @param   string $name The meta name.
	 * @param   mixed $value The meta value.
	 * @return  void
	 */
	public function setpostmeta( $id, $name, $value ) {
		if ( isset( $this->lookup[$id] ) ) {
			if ( isset( $this->lookup[$id][$name] ) ) {
				unset( $this->lookup[$id][$name] );
			}
		}
		update_post_meta( $id, 'totallycriticalcss_' . $name, $value );
	}
	
	/**
	 * get
	 *
	 * Sugar function to save options meta.
	 *
	 * @param   string $name The meta name.
	 * @return  mixed The meta value
	 */
	public function get( $name, $default=null ) {
		if ( isset( $this->lookup[$name] ) ) {
			return $this->lookup[$name];
		}
		$value = get_option( 'totallycriticalcss_' . $name );
		return $value ? $value : $default;
	}
	
	/**
	 * set
	 *
	 * Sugar function to save post meta.
	 *
	 * @param   string $name The meta name.
	 * @param   mixed $value The meta value.
	 * @param   boolean $autoload Autoload setting.
	 * @return  void
	 */
	public function set( $name, $value, $autoload=true ) {
		if ( isset( $this->lookup[$name] ) ) {
			unset( $this->lookup[$name] );
		}
		update_option( 'totallycriticalcss_' . $name, $value, $autoload );
	}

}
