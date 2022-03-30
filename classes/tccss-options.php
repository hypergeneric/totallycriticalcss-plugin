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
	 * getmeta
	 *
	 * Sugar function to save post meta.
	 *
	 * @param   int $id The post id.
	 * @param   string $name The meta name.
	 * @return  mixed The meta value
	 */
	public function getmeta( $id, $name, $default=null ) {
		/*if ( isset( $this->lookup[$id] ) ) {
			if ( isset( $this->lookup[$id][$name] ) ) {
				return $this->lookup[$id][$name];
			}
		}*/
		$value = get_post_meta( $id, 'totallycriticalcss_' . $name, true );
		return $value ? $value : $default;
	}
	
	/**
	 * setmeta
	 *
	 * Sugar function to save post meta.
	 *
	 * @param   int $id The post id.
	 * @param   string $name The meta name.
	 * @param   mixed $value The meta value.
	 * @return  void
	 */
	public function setmeta( $id, $name, $value ) {
		/*if ( isset( $this->lookup[$id] ) ) {
			if ( isset( $this->lookup[$id][$name] ) ) {
				unset( $this->lookup[$id][$name] );
			}
		}*/
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
		/*if ( isset( $this->lookup[$name] ) ) {
			return $this->lookup[$name];
		}*/
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
		/*if ( isset( $this->lookup[$name] ) ) {
			unset( $this->lookup[$name] );
		}*/
		update_option( 'totallycriticalcss_' . $name, $value, $autoload );
	}

}
