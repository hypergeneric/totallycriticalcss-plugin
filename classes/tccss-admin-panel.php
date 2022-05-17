<?php

class TCCSS_AdminPanel {
	
	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'wp_ajax_totallycriticalcss_save_admin_page', array( $this, 'save_admin_page' ) );
			add_action( 'wp_ajax_totallycriticalcss_add_custum_dequeue', array( $this, 'add_custum_dequeue' ) );
			add_action( 'wp_ajax_totallycriticalcss_delete_custum_dequeue', array( $this, 'delete_custum_dequeue' ) );
			add_action( 'wp_ajax_totallycriticalcss_add_custum_route', array( $this, 'add_custum_route' ) );
			add_action( 'wp_ajax_totallycriticalcss_delete_custum_route', array( $this, 'delete_custum_route' ) );
			add_action( 'wp_ajax_totallycriticalcss_add_ignore_route', array( $this, 'add_ignore_route' ) );
			add_action( 'wp_ajax_totallycriticalcss_delete_ignore_route', array( $this, 'delete_ignore_route' ) );
		}
	}
	
	/**
	 * save_admin_page
	 *
	 * Update Form Data when submitted
	 *
	 * @param   void
	 * @return  void
	 */
	public function save_admin_page() {
		
		$literals = [ 'api_key', 'selected_styles', 'selected_cpt', 'viewport_width', 'viewport_height' ];
		$bools = [ 'simplemode', 'show_metaboxes', 'adminmode' ];
		
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
		tccss()->sheetlist()->set_checksum();
		
	}
	
	/**
	 * add_custum_dequeue
	 *
	 * Add custom dequeue.
	 *
	 * @param   void
	 * @return  void
	 */
	public function add_custum_dequeue() {
		
		$form_handle = $_POST[ 'form_handle' ];
		$form_url = $_POST[ 'form_url' ];
		
		$custom_dequeue = tccss()->options()->get( 'custom_dequeue', [] );
		$custom_dequeue[$form_handle] = $form_url;
		
		tccss()->options()->set( 'custom_dequeue', $custom_dequeue );
		tccss()->plugin()->clear_tccss_data();
		
		wp_send_json_success( $custom_dequeue );
		
	}
	
	/**
	 * delete_custum_dequeue
	 *
	 * Delete custom dequeue.
	 *
	 * @param   void
	 * @return  void
	 */
	public function delete_custum_dequeue() {
		
		$form_handle = $_POST[ 'form_handle' ];
		
		$custom_dequeue = tccss()->options()->get( 'custom_dequeue', [] );
		unset( $custom_dequeue[$form_handle] );
		
		tccss()->options()->set( 'custom_dequeue', $custom_dequeue );
		tccss()->plugin()->clear_tccss_data();
		
		wp_send_json_success( $custom_dequeue );
		
	}
	
	/**
	 * add_custum_route
	 *
	 * Add custom route.
	 *
	 * @param   void
	 * @return  void
	 */
	public function add_custum_route() {
		
		$form_url = $_POST[ 'form_url' ];
		
		$custom_routes = tccss()->options()->get( 'custom_routes', [] );
		$custom_routes[] = $form_url;
		
		tccss()->options()->set( 'custom_routes', $custom_routes );
		tccss()->plugin()->clear_tccss_data();
		
		wp_send_json_success( $custom_routes );
		
	}
	
	/**
	 * delete_custum_route
	 *
	 * Delete custom route.
	 *
	 * @param   void
	 * @return  void
	 */
	public function delete_custum_route() {
		
		$form_url = $_POST[ 'form_url' ];
		
		$custom_routes = tccss()->options()->get( 'custom_routes', [] );
		array_splice( $custom_routes, array_search( $form_url, $custom_routes ), 1) ;
		
		tccss()->options()->set( 'custom_routes', $custom_routes );
		tccss()->plugin()->clear_tccss_data();
		
		wp_send_json_success( $custom_routes );
		
	}
	
	/**
	 * add_ignore_route
	 *
	 * Add ignore route.
	 *
	 * @param   void
	 * @return  void
	 */
	public function add_ignore_route() {
		
		$form_url = $_POST[ 'form_url' ];
		
		$ignore_routes = tccss()->options()->get( 'ignore_routes', [] );
		$ignore_routes[] = $form_url;
		
		tccss()->options()->set( 'ignore_routes', $ignore_routes );
		tccss()->plugin()->clear_tccss_data();
		
		wp_send_json_success( $ignore_routes );
		
	}
	
	/**
	 * delete_ignore_route
	 *
	 * Delete custom route.
	 *
	 * @param   void
	 * @return  void
	 */
	public function delete_ignore_route() {
		
		$form_url = $_POST[ 'form_url' ];
		
		$ignore_routes = tccss()->options()->get( 'ignore_routes', [] );
		array_splice( $ignore_routes, array_search( $form_url, $ignore_routes ), 1) ;
		
		tccss()->options()->set( 'ignore_routes', $ignore_routes );
		tccss()->plugin()->clear_tccss_data();
		
		wp_send_json_success( $ignore_routes );
		
	}

}
