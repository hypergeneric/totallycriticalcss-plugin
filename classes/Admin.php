<?php

namespace Classes\Admin;

class Setup {
	
	public function __construct() {
		if ( is_admin() ) {
			add_filter( 'plugin_action_links_' . basename( dirname( TOTALLYCRITICALCSS_PLUGIN_FILE ) ) . '/totallycriticalcss.php', array( $this, 'add_settings_link' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_page' ) );
			add_action( 'wp_ajax_totallycriticalcss_save_admin_page', array( $this, 'save_admin_page' ) );
			add_action( 'wp_ajax_totallycriticalcss_add_custum_dequeue', array( $this, 'add_custum_dequeue' ) );
			add_action( 'wp_ajax_totallycriticalcss_delete_custum_dequeue', array( $this, 'delete_custum_dequeue' ) );
			add_action( 'wp_ajax_totallycriticalcss_add_custum_route', array( $this, 'add_custum_route' ) );
			add_action( 'wp_ajax_totallycriticalcss_delete_custum_route', array( $this, 'delete_custum_route' ) );
		}
	}
	
	/**
	* Add settings link on plugin page
	*/
	public function get_admin_url() {
		return admin_url( 'options-general.php?page=' . basename( dirname( TOTALLYCRITICALCSS_PLUGIN_FILE ) ) );
	}

	/**
	* Add settings link on plugin page
	*/
	public function add_settings_link( $links ) {
		$links[] = '<a href="' . $this->get_admin_url() . '">' . __( 'Settings' ) . '</a>';
		return $links;
	}

	/**
	* Register and enqueue admin stylesheet & scripts
	*/
	public function admin_init() {
		// only enqueue these things on the settings page
		if ( wc_get_current_admin_url() == $this->get_admin_url() ) {
			wp_register_style( 'totallycriticalcss_plugin_stylesheet', plugin_dir_url( TOTALLYCRITICALCSS_PLUGIN_FILE ) . 'admin/css/admin.css' );
			wp_enqueue_style( 'totallycriticalcss_plugin_stylesheet' );
			wp_register_script( 'totallycriticalcss_script', plugin_dir_url( TOTALLYCRITICALCSS_PLUGIN_FILE ) . 'admin/js/admin.js', array( 'jquery' ) );
			wp_localize_script( 'totallycriticalcss_script', 'totallycriticalcss_obj',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' )
				)
			);
			wp_enqueue_script( 'totallycriticalcss_script' );
		}
	}

	/**
	* Register admin page and menu.
	*/
	public function admin_page() {
		add_submenu_page(
			'options-general.php',
			'Totally Critical CSS',
			'Totally Critical CSS',
			'administrator',
			dirname( TOTALLYCRITICALCSS_PLUGIN_FILE ),
			array( $this, 'admin_page_settings' ),
			100
		);
	}

	/**
	* Rebder admin view
	*/
	public function admin_page_settings() {
		require_once dirname( TOTALLYCRITICALCSS_PLUGIN_FILE ) . '/admin/view.php';
	}

	/**
	* Update Form Data when submitted
	*/
	public function save_admin_page() {
		
		$literals = [ 'api_key', 'selected_styles', 'selected_cpt' ];
		$bools = [ 'simplemode', 'show_metaboxes', 'always_immediate', 'adminmode' ];
		
		foreach ( $literals as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_option( 'totallycriticalcss_' . $key, $_POST[ $key ] );
			}
		}
		
		foreach ( $bools as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_option( 'totallycriticalcss_' . $key, $_POST[ $key ] == 'true' );
			}
		}
		
		\TotallyCriticalCSS::clear_tccss_data();
		
	}
	
	public function add_custum_dequeue() {
		
		$form_handle = $_POST[ 'form_handle' ];
		$form_url = $_POST[ 'form_url' ];
		
		$custom_dequeue = get_option( 'totallycriticalcss_custom_dequeue' ) === false ? [] : get_option( 'totallycriticalcss_custom_dequeue' );
		$custom_dequeue[$form_handle] = $form_url;
		
		update_option( 'totallycriticalcss_custom_dequeue', $custom_dequeue );
		\TotallyCriticalCSS::clear_tccss_data();
		
		wp_send_json_success( $custom_dequeue );
		
	}
	
	public function delete_custum_dequeue() {
		
		$form_handle = $_POST[ 'form_handle' ];
		
		$custom_dequeue = get_option( 'totallycriticalcss_custom_dequeue' ) === false ? [] : get_option( 'totallycriticalcss_custom_dequeue' );
		unset( $custom_dequeue[$form_handle] );
		
		update_option( 'totallycriticalcss_custom_dequeue', $custom_dequeue );
		\TotallyCriticalCSS::clear_tccss_data();
		
		wp_send_json_success( $custom_dequeue );
		
	}
	
	public function add_custum_route() {
		
		$form_url = $_POST[ 'form_url' ];
		
		$custom_routes = get_option( 'totallycriticalcss_custom_routes' ) === false ? [] : get_option( 'totallycriticalcss_custom_routes' );
		$custom_routes[] = $form_url;
		
		update_option( 'totallycriticalcss_custom_routes', $custom_routes );
		
		wp_send_json_success( $custom_routes );
		
	}
	
	public function delete_custum_route() {
		
		$form_url = $_POST[ 'form_url' ];
		
		$custom_routes = get_option( 'totallycriticalcss_custom_routes' ) === false ? [] : get_option( 'totallycriticalcss_custom_routes' );
		array_splice( $custom_routes, array_search( $form_url, $custom_routes ), 1) ;
		
		update_option( 'totallycriticalcss_custom_routes', $custom_routes );
		
		wp_send_json_success( $custom_routes );
		
	}

}
