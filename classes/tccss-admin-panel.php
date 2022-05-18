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
	 * status_invalidate
	 *
	 * Invalidate a route or id
	 *
	 * @param   void
	 * @return  void
	 */
	public function status_invalidate() {
		
		$route_or_id = $_POST[ 'route_or_id' ];
		$type = $_POST[ 'type' ];
		
		tccss()->options()->setmeta( $type, $route_or_id, 'checksum', false );
		tccss()->options()->setmeta( $type, $route_or_id, 'criticalcss', false );
		tccss()->options()->setmeta( $type, $route_or_id, 'invalidate', false );
		
		$this->get_status();
		
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
		
		$records = $wpdb->get_results( "SELECT * FROM {$wpdb->options} WHERE `option_name` LIKE 'totallycriticalcss_route_%'" );
		foreach ( $records as $record ) {
			$route = explode( "_", $record->option_name );
			$route = array_slice( $route, 2 );
			$route = implode( "/", $route );
			$route_display = wp_parse_url( home_url( $route ), PHP_URL_PATH );
			$data = unserialize( $record->option_value );
			$state = 'pending';
			if ( isset( $data['invalidate'] ) ) {
				if ( $data['invalidate'] == 'loading' ) {
					$state = 'processing';
				} else {
					if ( isset( $data['criticalcss'] ) ) {
						$criticalcss = $data['criticalcss'];
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
			}
			$status[$route_display] = [
				'route_or_id' => $route,
				'type' => 'route',
				'state' => $state
			];
		}
		
		$the_query = new WP_Query( [
			'post_type' => 'any',
			'post_status' => 'publish',
			'meta_query' => [
				'relation' => 'AND',
				[
					'key' => 'totallycriticalcss_invalidate',
					'compare' => 'EXISTS'
				],
			]
		] );
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$route_display = get_permalink( get_the_ID() );
				$route_display = wp_parse_url( $route_display, PHP_URL_PATH );
				$invalidate = tccss()->options()->getpostmeta( get_the_ID(), 'invalidate' );
				$criticalcss = tccss()->options()->getpostmeta( get_the_ID(), 'criticalcss', null );
				$state = 'pending';
				if ( $invalidate == 'loading' ) {
					$state = 'processing';
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
