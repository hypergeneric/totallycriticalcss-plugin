<?php

class TCCSS_AdminPanel {
	
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'wp_ajax_totallycriticalcss_save_admin_page', array( $this, 'save_admin_page' ) );
			add_action( 'wp_ajax_totallycriticalcss_add_custum_dequeue', array( $this, 'add_custum_dequeue' ) );
			add_action( 'wp_ajax_totallycriticalcss_delete_custum_dequeue', array( $this, 'delete_custum_dequeue' ) );
			add_action( 'wp_ajax_totallycriticalcss_add_custum_route', array( $this, 'add_custum_route' ) );
			add_action( 'wp_ajax_totallycriticalcss_delete_custum_route', array( $this, 'delete_custum_route' ) );
		}
	}

	/**
	* Update Form Data when submitted
	*/
	public function save_admin_page() {
		
		$literals = [ 'api_key', 'selected_styles', 'selected_cpt' ];
		$bools = [ 'simplemode', 'show_metaboxes', 'always_immediate', 'adminmode' ];
		
		foreach ( $literals as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				tccss()->options()->set( $key, $_POST[ $key ] );
			}
		}
		
		foreach ( $bools as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				tccss()->options()->set( $key, $_POST[ $key ] == 'true' );
			}
		}
		
		tccss()->plugin()->clear_tccss_data();
		
	}
	
	public function add_custum_dequeue() {
		
		$form_handle = $_POST[ 'form_handle' ];
		$form_url = $_POST[ 'form_url' ];
		
		$custom_dequeue = tccss()->options()->get( 'custom_dequeue', [] );
		$custom_dequeue[$form_handle] = $form_url;
		
		tccss()->options()->set( 'custom_dequeue', $custom_dequeue );
		tccss()->plugin()->clear_tccss_data();
		
		wp_send_json_success( $custom_dequeue );
		
	}
	
	public function delete_custum_dequeue() {
		
		$form_handle = $_POST[ 'form_handle' ];
		
		$custom_dequeue = tccss()->options()->get( 'custom_dequeue', [] );
		unset( $custom_dequeue[$form_handle] );
		
		tccss()->options()->set( 'custom_dequeue', $custom_dequeue );
		tccss()->plugin()->clear_tccss_data();
		
		wp_send_json_success( $custom_dequeue );
		
	}
	
	public function add_custum_route() {
		
		$form_url = $_POST[ 'form_url' ];
		
		$custom_routes = tccss()->options()->get( 'custom_routes', [] );
		$custom_routes[] = $form_url;
		
		tccss()->options()->set( 'custom_routes', $custom_routes );
		
		wp_send_json_success( $custom_routes );
		
	}
	
	public function delete_custum_route() {
		
		$form_url = $_POST[ 'form_url' ];
		
		$custom_routes = tccss()->options()->get( 'custom_routes', [] );
		array_splice( $custom_routes, array_search( $form_url, $custom_routes ), 1) ;
		
		tccss()->options()->set( 'custom_routes', $custom_routes );
		
		wp_send_json_success( $custom_routes );
		
	}

}
