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
			add_action( 'wp_ajax_totallycriticalcss_get_status', array( $this, 'get_status' ) );
			add_action( 'wp_ajax_totallycriticalcss_status_invalidate', array( $this, 'status_invalidate' ) );
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
		
		$post_clean = filter_input_array( INPUT_POST, [
			'api_key'         => FILTER_SANITIZE_ENCODED,
			'viewport_width'  => FILTER_SANITIZE_NUMBER_INT,
			'viewport_height' => FILTER_SANITIZE_NUMBER_INT,
			'simplemode'      => FILTER_VALIDATE_BOOLEAN,
			'show_metaboxes'  => FILTER_VALIDATE_BOOLEAN,
			'adminmode'       => FILTER_VALIDATE_BOOLEAN,
			'selected_styles' => [
				'filter'     => FILTER_SANITIZE_STRING,
				'flags'      => FILTER_REQUIRE_ARRAY,
			],
			'selected_cpt' => [
				'filter'     => FILTER_SANITIZE_STRING,
				'flags'      => FILTER_REQUIRE_ARRAY,
			]
		] );
		
		foreach ( $literals as $key ) {
			if ( isset( $post_clean[ $key ] ) ) {
				tccss()->options()->set( $key, $post_clean[ $key ] );
			}
		}
		
		foreach ( $bools as $key ) {
			if ( isset( $post_clean[ $key ] ) ) {
				tccss()->options()->set( $key, $post_clean[ $key ] == 'true' );
			}
		}
		
		tccss()->plugin()->clear_tccss_data();
		tccss()->sheetlist()->set_checksum();
		
	}
	
	/**
	 * status_invalidate
	 *
	 * Invalidate a route or id
	 *
	 * @param   void
	 * @return  void
	 */
	public function status_invalidate() {
		
		$route_or_id = filter_input( INPUT_POST, 'route_or_id', FILTER_SANITIZE_URL );
		$type        = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );
		
		tccss()->options()->setmeta( $type, $route_or_id, 'checksum', false );
		tccss()->options()->setmeta( $type, $route_or_id, 'criticalcss', false );
		tccss()->options()->setmeta( $type, $route_or_id, 'invalidate', false );
		
		$this->get_status();
		
	}
	
	/**
	 * get_status_string
	 *
	 * Get a status based on meta
	 *
	 * @param   void
	 * @return  void
	 */
	public function get_status_string( $invalidate, $retry, $criticalcss ) {
		$state = 'pending';
		if ( $invalidate == 'loading' ) {
			$state = 'processing';
		} else {
			if ( $retry ) {
				$state = 'retry';
			} else {
				if ( $criticalcss ) {
					if ( $criticalcss->success === true ) {
						$state = 'generated';
					} else if ( $criticalcss->success === false ) {
						$state = 'error';
					} else {
						$state = 'error';
					}
				}
			}
		}
		return $state;
	}
	
	/**
	 * get_status
	 *
	 * Get a list of all pages and routes
	 *
	 * @param   void
	 * @return  void
	 */
	public function get_status() {
		
		global $wpdb;
		
		$status = [];
		
		// pull the routes from the options table
		$records = $wpdb->get_results( "SELECT * FROM {$wpdb->options} WHERE `option_name` LIKE 'totallycriticalcss_route_%'" );
		foreach ( $records as $record ) {
			$route         = explode( "_", $record->option_name );
			$route         = array_slice( $route, 2 );
			$route         = implode( "/", $route );
			$route_display = wp_parse_url( home_url( $route ), PHP_URL_PATH );
			$data          = unserialize( $record->option_value );
			$invalidate    = isset( $data['invalidate'] ) ? $data['invalidate'] : false;
			$retry         = isset( $data['retry'] ) ? $data['retry'] : false;
			$criticalcss   = isset( $data['criticalcss'] ) ? $data['criticalcss'] : false;
			$state         = $this->get_status_string( $invalidate, $retry, $criticalcss );
			$status[$route_display] = [
				'route_or_id' => $route,
				'type' => 'route',
				'state' => $state
			];
		}
		
		// pull the routes from the posts table
		$the_query = new WP_Query( [
			'post_type'         => 'any',
			'posts_per_page'    => -1,
			'post_status'       => 'publish',
			'meta_query'        => [
				'relation'     => 'AND',
				[
					'key'     => 'totallycriticalcss_invalidate',
					'compare' => 'EXISTS'
				],
			]
		] );
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) { $the_query->the_post();
				$route_display = get_permalink( get_the_ID() );
				$route_display = wp_parse_url( $route_display, PHP_URL_PATH );
				$invalidate    = tccss()->options()->getpostmeta( get_the_ID(), 'invalidate' );
				$criticalcss   = tccss()->options()->getpostmeta( get_the_ID(), 'criticalcss', null );
				$retry         = tccss()->options()->getpostmeta( get_the_ID(), 'retry' );
				$state         = $this->get_status_string( $invalidate, $retry, $criticalcss );
				$status[$route_display] = [
					'route_or_id' => get_the_ID(),
					'type' => 'single',
					'state' => $state
				];
			}
		}
		wp_reset_postdata();
		
		wp_send_json_success( $status );
		
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
		
		$form_handle = filter_input( INPUT_POST, 'form_handle', FILTER_SANITIZE_STRING );
		$form_url    = filter_input( INPUT_POST, 'form_url', FILTER_SANITIZE_URL );
		
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
		
		$form_handle = filter_input( INPUT_POST, 'form_handle', FILTER_SANITIZE_STRING );
		
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
		
		$form_url = filter_input( INPUT_POST, 'form_url', FILTER_SANITIZE_URL );
		
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
		
		$form_url = filter_input( INPUT_POST, 'form_url', FILTER_SANITIZE_URL );
		
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
		
		$form_url = filter_input( INPUT_POST, 'form_url', FILTER_SANITIZE_URL );
		
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
		
		$form_url = filter_input( INPUT_POST, 'form_url', FILTER_SANITIZE_URL );
		
		$ignore_routes = tccss()->options()->get( 'ignore_routes', [] );
		array_splice( $ignore_routes, array_search( $form_url, $ignore_routes ), 1) ;
		
		tccss()->options()->set( 'ignore_routes', $ignore_routes );
		tccss()->plugin()->clear_tccss_data();
		
		wp_send_json_success( $ignore_routes );
		
	}

}
