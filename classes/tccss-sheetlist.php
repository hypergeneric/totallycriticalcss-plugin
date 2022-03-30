<?php

class TCCSS_Sheetlist {

	public function __construct() {
	}
	
	/**
	* Get all the current stylesheets fro the homepage.
	*/
	public function get_current() {

		$sheets = [];
		$response = wp_remote_get( get_home_url() . "/?totallycriticalcss=preview" );
		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$body = $response[ 'body' ];
			$doc  = new DOMDocument();
			$doc->loadHTML( $body, LIBXML_NOWARNING | LIBXML_NOERROR );
			$domcss = $doc->getElementsByTagName( 'link' );
			foreach ( $domcss as $links ) {
				if ( strtolower( $links->getAttribute( 'rel' ) ) == "stylesheet" ) {
					$sheets[] = $links;
				}
			}
		}
		$result = [];
		foreach ( $sheets as $sheet ) { 
			$sheetid = $sheet->getAttribute('id');
			$sheetid_bits = explode( '-', $sheetid );
			array_pop( $sheetid_bits );
			$sheetid_clean = implode( '-', $sheetid_bits );
			$result[$sheetid_clean] = $sheet->getAttribute( 'href' );
		}
		return $result;

	}
	
	/**
	* Get the stylesheets to enqueue/dequeue
	*/
	public function get_selected() {
		
		$simplemode = tccss()->options()->get( 'simplemode' );
		$css = [];
		
		if ( $simplemode ) {
			
			// if we are using simplemode, get all the current handles, and save them to a transient
			// also, save a hash of the data plus a timestamp to check against for future calls
			// we are going to do this no more than once a day
			$sheetlist = get_transient( 'totallycriticalcss-sheetlist' );
			if ( ! $sheetlist ) {
				$sheetlist = tccss()->sheetlist()->get_current();;
				set_transient( 'totallycriticalcss-sheetlist', $sheetlist, 86400 );
				set_transient( 'totallycriticalcss-sheetlist-checksum', md5( serialize ( $sheetlist ) ), 86400 );
			}
			foreach ( $sheetlist as $handle => $url ) {
				$css = $sheetlist;
			}
			
		} else {
			
			$custom_dequeue  = tccss()->options()->get( 'custom_dequeue', [] );
			foreach ( $custom_dequeue as $handle => $url ) {
				$css[$handle] = $url;
			}
			$selected_styles = tccss()->options()->get( 'selected_styles', [] );
			foreach ( $selected_styles as $handle => $url ) {
				$css[$handle] = $url;
			}
			
		}
		return $css;
	}

}
