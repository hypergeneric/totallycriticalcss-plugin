<?php

namespace Classes\Admin;

class Setup {

	public function totallycriticalcss_admin_setup() {
		if ( is_admin() ) {
			// we are in admin mode
			add_filter( 'plugin_action_links_' . basename( dirname( TOTALLYCRITICALCSS_PLUGIN_FILE ) ) . '/totallycriticalcss.php', array( $this, 'totallycriticalcss_add_settings_link' ) );
			add_action( 'admin_init', array( $this, 'totallycriticalcss_admin_styles' ) );
			add_action( 'admin_init', array( $this, 'totallycriticalcss_admin_scripts' ) );
			add_action( 'admin_menu', array( $this, 'totallycriticalcss_admin_page' ) );
			add_action( 'wp_ajax_nopriv_totallycriticalcss_save_admin_page', array( $this, 'totallycriticalcss_save_admin_page' ) );
			add_action( 'wp_ajax_totallycriticalcss_save_admin_page', array( $this, 'totallycriticalcss_save_admin_page' ) );
			add_action( 'wp_ajax_totallycriticalcss_add_custum_dequeue', array( $this, 'totallycriticalcss_add_custum_dequeue' ) );
			add_action( 'wp_ajax_totallycriticalcss_delete_custum_dequeue', array( $this, 'totallycriticalcss_delete_custum_dequeue' ) );
		}
	}

	/**
	* Add settings link on plugin page
	*/
	public function totallycriticalcss_add_settings_link( $links ) {
		$links[] = '<a href="' . admin_url( 'options-general.php?page=' . basename( dirname( TOTALLYCRITICALCSS_PLUGIN_FILE ) ) ) . '">' . __( 'Settings' ) . '</a>';
		return $links;
	}

	/**
	* Register and enqueue admin stylesheet
	*/
	public function totallycriticalcss_admin_styles() {
		wp_register_style( 'totallycriticalcss_plugin_stylesheet', plugin_dir_url( TOTALLYCRITICALCSS_PLUGIN_FILE ) . 'admin/css/admin.css' );
		wp_enqueue_style( 'totallycriticalcss_plugin_stylesheet' );
	}

	/**
	* Register and enqueue admin scripts
	*/
	public function totallycriticalcss_admin_scripts() {
		wp_register_script( 'totallycriticalcss_script', plugin_dir_url( TOTALLYCRITICALCSS_PLUGIN_FILE ) . 'admin/js/admin.js', array( 'jquery' ) );
		wp_localize_script( 'totallycriticalcss_script', 'totallycriticalcss_obj',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			)
		);
		wp_enqueue_script( 'totallycriticalcss_script' );
	}

	/**
	* Register admin page and menu.
	*/
	public function totallycriticalcss_admin_page() {
		add_submenu_page(
			'options-general.php',
			'Totally Critical CSS',
			'Totally Critical CSS',
			'administrator',
			dirname( TOTALLYCRITICALCSS_PLUGIN_FILE ),
			array( $this, 'totallycriticalcss_admin_page_settings' ),
			100
		);
	}

	public function totallycriticalcss_admin_page_settings() {
		require_once dirname( TOTALLYCRITICALCSS_PLUGIN_FILE ) . '/admin/view.php';
	}

	/**
	* Update Form Data when submitted
	*/
	public function totallycriticalcss_save_admin_page() {
		
		$api_key = $_POST[ 'api_key' ];
		if( $api_key ) {
			update_option( 'totallycriticalcss_api_key', $api_key );
		} else {
			delete_option( 'totallycriticalcss_api_key' );
		}

		$custom_stylesheet = $_POST[ 'custom_stylesheet' ];
		if( $custom_stylesheet ) {
			update_option( 'totallycriticalcss_custom_stylesheet_location', $custom_stylesheet );
		} else {
			delete_option( 'totallycriticalcss_custom_stylesheet_location' );
		}

		$selected_styles = $_POST[ 'selected_styles' ];
		if( $selected_styles ) {
			update_option( 'totallycriticalcss_selected_styles', $selected_styles );
		} else {
			delete_option( 'totallycriticalcss_selected_styles' );
		}
		
		$my_post_types = $_POST[ 'my_post_types' ];
		if( $my_post_types ) {
			update_option( 'totallycriticalcss_selected_cpt', $my_post_types );
		} else {
			delete_option( 'totallycriticalcss_selected_cpt' );
		}
		
	}
	
	public function totallycriticalcss_add_custum_dequeue() {
		
		$form_handle = $_POST[ 'form_handle' ];
		$form_url = $_POST[ 'form_url' ];
		
		$custom_dequeue = get_option( 'totallycriticalcss_custom_dequeue' );
		$custom_dequeue[$form_handle] = $form_url;
		
		update_option( 'totallycriticalcss_custom_dequeue', $custom_dequeue );
		
		wp_send_json_success( $custom_dequeue );
		
	}
	
	public function totallycriticalcss_delete_custum_dequeue() {
		
		$form_handle = $_POST[ 'form_handle' ];
		
		$custom_dequeue = get_option( 'totallycriticalcss_custom_dequeue' );
		unset( $custom_dequeue[$form_handle] );
		
		update_option( 'totallycriticalcss_custom_dequeue', $custom_dequeue );
		
		wp_send_json_success( $custom_dequeue );
		
	}

}
