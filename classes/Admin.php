<?php

namespace Classes\Admin;

class Setup {

	public function totallycriticalcss_admin_setup() {
		if ( is_admin() ) {
			// we are in admin mode
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'totallycriticalcss_add_settings_link' ) );
			add_action( 'admin_init', array( $this, 'totallycriticalcss_admin_styles' ) );
			add_action( 'admin_init', array( $this, 'totallycriticalcss_admin_scripts' ) );
			add_action( 'admin_menu', array( $this, 'totallycriticalcss_admin_page' ) );
			add_action( 'wp_ajax_nopriv_totallycriticalcss_save_admin_page', array( $this, 'totallycriticalcss_save_admin_page' ) );
			add_action( 'wp_ajax_totallycriticalcss_save_admin_page', array( $this, 'totallycriticalcss_save_admin_page' ) );
		}
	}

	/**
	* Add settings link on plugin page
	*/
	public function totallycriticalcss_add_settings_link( $links ) {
		$links[] = '<a href="' . admin_url( 'options-general.php?page=totallycriticalcss' ) . '">' . __( 'Settings' ) . '</a>';
		return $links;
	}

	/**
	* Register and enqueue admin stylesheet
	*/
	public function totallycriticalcss_admin_styles() {
		wp_register_style( 'totallycriticalcss_plugin_stylesheet', plugin_dir_url(__FILE__) . '../admin/css/admin.css' );
		wp_enqueue_style( 'totallycriticalcss_plugin_stylesheet' );
	}

	/**
	* Register and enqueue admin scripts
	*/
	public function totallycriticalcss_admin_scripts() {
		wp_register_script( 'totallycriticalcss_script', plugin_dir_url(__FILE__) . '../admin/js/admin.js', array( 'jquery' ) );
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
			dirname(__FILE__, 2),
			array( $this, 'totallycriticalcss_admin_page_settings' ),
			100
		);
	}

	public function totallycriticalcss_admin_page_settings() {
		require_once __DIR__ . '/../admin/view.php';
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

		$custom_theme = $_POST[ 'custom_theme' ];
		if( $custom_theme ) {
			update_option( 'totallycriticalcss_custom_theme_location', $custom_theme );
		} else {
			delete_option( 'totallycriticalcss_custom_theme_location' );
		}

		$custom_stylesheet = $_POST[ 'custom_stylesheet' ];
		if( $custom_stylesheet ) {
			update_option( 'totallycriticalcss_custom_stylesheet_location', $custom_stylesheet );
		} else {
			delete_option( 'totallycriticalcss_custom_stylesheet_location' );
		}

		$custom_dequeue = $_POST[ 'custom_dequeue' ];
		if( $custom_dequeue ) {
			update_option( 'totallycriticalcss_custom_dequeue', $custom_dequeue );
		} else {
			delete_option( 'totallycriticalcss_custom_dequeue' );
		}

		$selected_styles = $_POST[ 'selected_styles' ];
		if( $selected_styles ) {
			update_option( 'totallycriticalcss_selected_styles', $selected_styles );
		} else {
			delete_option( 'totallycriticalcss_selected_styles' );
		}
	}

}
