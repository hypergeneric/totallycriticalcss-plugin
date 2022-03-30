<?php

class TCCSS_Options {
	
	var $lookup = [];

	public function __construct() {
	}
	
	public function getmeta( $id, $name, $default=null ) {
		/*if ( isset( $this->lookup[$id] ) ) {
			if ( isset( $this->lookup[$id][$name] ) ) {
				return $this->lookup[$id][$name];
			}
		}*/
		$value = get_post_meta( $id, 'totallycriticalcss_' . $name, true );
		return $value ? $value : $default;
	}
	
	public function setmeta( $id, $name, $value ) {
		/*if ( isset( $this->lookup[$id] ) ) {
			if ( isset( $this->lookup[$id][$name] ) ) {
				unset( $this->lookup[$id][$name] );
			}
		}*/
		update_post_meta( $id, 'totallycriticalcss_' . $name, $value );
	}
	
	public function get( $name, $default=null ) {
		/*if ( isset( $this->lookup[$name] ) ) {
			return $this->lookup[$name];
		}*/
		$value = get_option( 'totallycriticalcss_' . $name );
		return $value ? $value : $default;
	}
	
	public function set( $name, $value, $autoload=true ) {
		/*if ( isset( $this->lookup[$name] ) ) {
			unset( $this->lookup[$name] );
		}*/
		update_option( 'totallycriticalcss_' . $name, $value, $autoload );
	}

}
